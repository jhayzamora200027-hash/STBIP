<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\NoMarkup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    private const OTP_SEND_LIMIT = 3;
    private const OTP_VERIFY_LIMIT = 3;
    private const OTP_SEND_LOCK_MINUTES = 5;

    private function otpThrottleKey(int|string $userId, string $suffix): string
    {
        return 'otp_throttle:' . $suffix . ':' . $userId;
    }

    private function getOtpSendCount(Request $request, ?int $userId = null): int
    {
        $userId = $userId ?? (int) $request->session()->get('otp_user_id');
        if ($userId > 0) {
            return (int) Cache::get($this->otpThrottleKey($userId, 'send_count'), 0);
        }

        return (int) $request->session()->get('otp_send_count', 0);
    }

    private function getOtpVerifyAttempts(Request $request, ?int $userId = null): int
    {
        $userId = $userId ?? (int) $request->session()->get('otp_user_id');
        if ($userId > 0) {
            return (int) Cache::get($this->otpThrottleKey($userId, 'verify_attempts'), 0);
        }

        return (int) $request->session()->get('otp_attempts', 0);
    }

    private function putOtpSendCount(Request $request, int $userId, int $count, ?Carbon $expiresAt = null): void
    {
        $ttl = $expiresAt ?? Carbon::now()->addMinutes(self::OTP_SEND_LOCK_MINUTES);
        Cache::put($this->otpThrottleKey($userId, 'send_count'), max(0, $count), $ttl);
        $request->session()->put('otp_send_count', max(0, $count));
    }

    private function putOtpVerifyAttempts(Request $request, int $userId, int $attempts, ?Carbon $expiresAt = null): void
    {
        $ttl = $expiresAt ?? Carbon::now()->addMinutes(self::OTP_SEND_LOCK_MINUTES);
        Cache::put($this->otpThrottleKey($userId, 'verify_attempts'), max(0, $attempts), $ttl);
        $request->session()->put('otp_attempts', max(0, $attempts));
    }

    private function clearOtpThrottle(Request $request, ?int $userId = null): void
    {
        $userId = $userId ?? (int) $request->session()->get('otp_user_id');
        if ($userId > 0) {
            Cache::forget($this->otpThrottleKey($userId, 'send_count'));
            Cache::forget($this->otpThrottleKey($userId, 'verify_attempts'));
            Cache::forget($this->otpThrottleKey($userId, 'locked_until'));
        }

        $request->session()->forget('otp_send_locked_until');
        $request->session()->put('otp_send_count', 0);
        $request->session()->put('otp_attempts', 0);
    }

    private function maskEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $parts = explode('@', $email);
        $local = $parts[0] ?? '';
        $domain = $parts[1] ?? '';

        if (strlen($local) <= 2) {
            $maskedLocal = substr($local, 0, 1) . '*';
        } else {
            $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 2)) . substr($local, -1);
        }

        return $maskedLocal . ($domain ? '@' . $domain : '');
    }

    private function getOtpLockPayload(Request $request): ?array
    {
        $userId = (int) $request->session()->get('otp_user_id');
        $lockedUntil = $userId > 0
            ? Cache::get($this->otpThrottleKey($userId, 'locked_until'))
            : $request->session()->get('otp_send_locked_until');
        if (!$lockedUntil) {
            return null;
        }

        try {
            $lockedUntilAt = Carbon::parse($lockedUntil);
        } catch (\Exception $e) {
            $this->clearOtpThrottle($request, $userId ?: null);
            return null;
        }

        if (Carbon::now()->gte($lockedUntilAt)) {
            $this->clearOtpThrottle($request, $userId ?: null);
            return null;
        }

        $request->session()->put('otp_send_locked_until', $lockedUntilAt->toDateTimeString());

        return [
            'locked_until' => $lockedUntilAt->toIso8601String(),
            'retry_after_seconds' => Carbon::now()->diffInSeconds($lockedUntilAt),
        ];
    }

    private function verifyRecaptchaToken(Request $request, ?string $token): bool
    {
        $secret = config('services.recaptcha.secret');
        if (empty($secret)) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            return (bool) ($verify->json('success') ?? false);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function otpSendStatePayload(Request $request, ?Carbon $expiresAt = null): array
    {
        $lock = $this->getOtpLockPayload($request);
        $sendCount = $this->getOtpSendCount($request);
        $maskedEmail = null;
        $userId = $request->session()->get('otp_user_id');

        $request->session()->put('otp_send_count', $sendCount);

        if ($userId) {
            $user = User::find($userId);
            $maskedEmail = $this->maskEmail($user?->email);
        }

        return [
            'masked_email' => $maskedEmail,
            'otp_sent' => (bool) $request->session()->get('otp_sent', false),
            'otp_expires_at' => $expiresAt ? $expiresAt->toIso8601String() : null,
            'send_count' => $sendCount,
            'send_limit' => self::OTP_SEND_LIMIT,
            'remaining_sends' => max(0, self::OTP_SEND_LIMIT - $sendCount),
            'locked_until' => $lock['locked_until'] ?? null,
            'retry_after_seconds' => $lock['retry_after_seconds'] ?? null,
        ];
    }

    private function otpJsonError(Request $request, string $message, int $status = 422, array $errors = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ] + $this->otpSendStatePayload($request), $status);
    }

    private function sendOtpForPendingLogin(Request $request, User $user): array
    {
        try {
            $otp = random_int(100000, 999999);
        } catch (\Exception $e) {
            $otp = rand(100000, 999999);
        }

        $expires = Carbon::now()->addMinutes(5);
        $request->session()->put('otp_code', (string) $otp);
        $request->session()->put('otp_expires_at', $expires->toDateTimeString());
        $request->session()->put('otp_attempts', 0);
        $request->session()->put('otp_sent', true);
        $nextSendCount = $this->getOtpSendCount($request, $user->id) + 1;
        $this->putOtpSendCount($request, $user->id, $nextSendCount);
        $this->putOtpVerifyAttempts($request, $user->id, 0, $expires);

        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email to ' . $user->email . ': ' . $e->getMessage());
        }

        return $this->otpSendStatePayload($request, $expires);
    }

    
    public function addUser(Request $request)
    {
        $emailForUserId = $request->email;
        $generatedUserId = null;
        if ($emailForUserId && strpos($emailForUserId, '@') !== false) {
            $generatedUserId = strstr($emailForUserId, '@', true);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'firstname' => ['required', 'string', 'max:255', new NoMarkup()],
                'middlename' => ['nullable', 'string', 'max:255', new NoMarkup()],
                'lastname' => ['required', 'string', 'max:255', new NoMarkup()],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->where(function ($query) {
                        $query->where(function ($q) {
                            $q->whereNull('approvalstatus')
                              ->orWhere('approvalstatus', '')
                              ->orWhere('approvalstatus', '!=', 'R');
                        });
                    }),
                    'regex:/^[A-Za-z0-9._%+-]+@dswd\.gov\.ph$/i',
                ],
                'usergroup' => 'required|in:admin,user,sysadmin',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
                    'confirmed',
                ],
                'g-recaptcha-response' => 'required',
            ],
            [
                'email.regex' => 'The email address must be a DSWD email (example: user@dswd.gov.ph).',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol.',
                'g-recaptcha-response.required' => 'Please complete the reCAPTCHA.',
            ]
        );

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator, 'adduser')
                ->withInput();
        }

        try {
            $fields = $validator->validated();

            $existingRejectedUser = User::where('email', $fields['email'])
                ->where('approvalstatus', 'R')
                ->first();

            if ($generatedUserId) {
                $userIdQuery = User::where('user_id', $generatedUserId);
                if ($existingRejectedUser) {
                    $userIdQuery->where('id', '!=', $existingRejectedUser->id);
                }
                if ($userIdQuery->exists()) {
                    $error = ['user_id' => ['The generated User ID already exists. Please use a different email address.']];
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Validation failed.',
                            'errors' => $error,
                        ], 422);
                    }
                    return redirect()->back()
                        ->withErrors($error, 'adduser')
                        ->withInput();
                }
            }

            $fields['password'] = bcrypt($fields['password']);
            $fields['user_id'] = $generatedUserId ?? ($existingRejectedUser->user_id ?? '');
            $fields['active'] = 1;
            $fields['approvalstatus'] = 'A';
            $fields['name'] = trim($fields['firstname'] . ' ' . ($fields['middlename'] ?? '') . ' ' . $fields['lastname']);
            $fields['name'] = preg_replace('/\s+/', ' ', $fields['name']);

            if ($existingRejectedUser) {
                $existingRejectedUser->fill($fields);
                $existingRejectedUser->approvalstatus = 'A';
                $existingRejectedUser->approvalcomment = null;
                $existingRejectedUser->approvedby = Auth::user() ? Auth::user()->name : null;
                $existingRejectedUser->save();
                $user = $existingRejectedUser;
            } else {
                $user = User::create($fields);
            }
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to add user: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to add user: ' . $e->getMessage())
                ->withInput();
        }
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Additional user added and approved successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => trim(($user->firstname ?? '') . ' ' . ($user->middlename ?? '') . ' ' . ($user->lastname ?? '')),
                    'email' => $user->email,
                    'usergroup' => $user->usergroup,
                    'active' => $user->active,
                    'created_at' => $user->created_at ? $user->created_at->format('M d, Y') : null,
                ],
            ]);
        }
        return redirect()->back()->with('success', 'Additional user added and approved successfully.');
    }
    /**
     * Ensure responses take at least a minimum amount of time to mitigate timing attacks.
     *
     * @param float $start microtime(true) start time
     * @param int $minMs minimum response time in milliseconds
     * @return void
     */
    private function ensureMinResponseTime(float $start, int $minMs = 700): void
    {
        try {
            $elapsed = (microtime(true) - $start) * 1000.0;
            $remaining = $minMs - $elapsed;
            if ($remaining > 0) {
                usleep((int)($remaining * 1000));
            }
        } catch (\Throwable $e) {
            // best-effort; don't let timing helper break the request
        }
    }
    public function register(Request $request)
    {
        $emailForUserId = $request->email;
        $generatedUserId = null;
        $start = microtime(true);
        if ($emailForUserId && strpos($emailForUserId, '@') !== false) {
            $generatedUserId = strstr($emailForUserId, '@', true);
        }

        $approvedEmailExists = User::where('email', $request->email)
            ->where('approvalstatus', 'A')
            ->exists();
        
        $approvedUserIdExists = $generatedUserId
            ? User::where('user_id', $generatedUserId)
                ->where('approvalstatus', 'A')
                ->exists()
            : false;
        
        if ($approvedEmailExists || $approvedUserIdExists) {
            $this->ensureMinResponseTime($start, 700);
            try {
                if ($approvedEmailExists) {
                    Mail::send('emails.existing_registration_attempt', [], function ($message) use ($request) {
                        $message->to($request->email)
                                ->subject('Security notice: Registration attempt - STB Inventory Portal');
                    });
                }
            } catch (\Exception $e) {
                Log::error('Failed to send existing-registration notification: ' . $e->getMessage());
            }

            $genericMsg = 'If this email is not yet registered, a verification link has been sent. If it is already registered, please check your inbox for login instructions.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $genericMsg,
                ]);
            }
            return redirect()->route('main')->with('success', $genericMsg);
        }

        $pendingEmail = User::where('email', $request->email)
            ->where(function($query) {
                $query->whereNull('approvalstatus')
                      ->orWhere('approvalstatus', '');
            })
            ->exists();

        $pendingUserId = $generatedUserId
            ? User::where('user_id', $generatedUserId)
                ->where(function($query) {
                    $query->whereNull('approvalstatus')
                          ->orWhere('approvalstatus', '');
                })
                ->exists()
            : false;

        // If there is a pending registration for the email or generated user_id,
        // return the same generic message as above to avoid account enumeration.
        if ($pendingEmail || $pendingUserId) {
            $this->ensureMinResponseTime($start, 700);
            $genericMsg = 'If this email is not yet registered, a verification link has been sent. If it is already registered, please check your inbox for login instructions.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $genericMsg,
                ]);
            }
            return redirect()->route('main')->with('success', $genericMsg);
        }
        
        $validator = Validator::make(
            $request->all(),
            [
                'firstname' => ['required', 'string', 'max:255', new NoMarkup()],
                'middlename' => ['nullable', 'string', 'max:255', new NoMarkup()],
                'lastname' => ['required', 'string', 'max:255', new NoMarkup()],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'regex:/^[A-Za-z0-9._%+-]+@dswd\.gov\.ph$/i',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
                    'confirmed',
                ],
            ],
            [
                'email.regex' => 'The email address must be a DSWD email (example: user@dswd.gov.ph).',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol.',
            ]
        );
        
        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator, 'register')
                ->withInput();
        }
        
        // Verify recaptcha before attempting to create the account
        if (!$validator->fails()) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            try {
                $recapSecret = config('services.recaptcha.secret');
                $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $recapSecret,
                    'response' => $recaptchaResponse,
                    'remoteip' => $request->ip(),
                ]);
                $body = $verify->json();
            } catch (\Exception $e) {
                $body = ['success' => false];
            }

            if (!($body['success'] ?? false)) {
                $this->ensureMinResponseTime($start, 700);
                $error = ['g-recaptcha-response' => ['reCAPTCHA verification failed.']];
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => $error,
                    ], 422);
                }
                return redirect()->back()->withErrors($error, 'register')->withInput();
            }
        }

        try {
            $IncomingFields = $validator->validated();
            $IncomingFields['password'] = bcrypt($IncomingFields['password']);
            $IncomingFields['active'] = 1; 
            $IncomingFields['name'] = trim($IncomingFields['firstname'] . ' ' . ($IncomingFields['middlename'] ?? '') . ' ' . $IncomingFields['lastname']);
            $IncomingFields['name'] = preg_replace('/\s+/', ' ', $IncomingFields['name']); 
            if (!$generatedUserId && isset($IncomingFields['email']) && strpos($IncomingFields['email'], '@') !== false) {
                $generatedUserId = strstr($IncomingFields['email'], '@', true);
            }
            $IncomingFields['user_id'] = $generatedUserId ?? '';

            $existingRejectedUser = User::where(function($query) use ($request, $generatedUserId) {
                $query->where('email', $request->email)
                      ->orWhere('user_id', $generatedUserId);
            })->where('approvalstatus', 'R')->first();

            if ($existingRejectedUser) {
                $existingRejectedUser->fill($IncomingFields);
                $existingRejectedUser->approvalstatus = null; 
                $existingRejectedUser->approvalcomment = null;
                $existingRejectedUser->approvedby = null;
                $existingRejectedUser->save();
            } else {
                User::create($IncomingFields);
            }
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Registration failed: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Registration failed: ' . $e->getMessage())
                ->withInput();
        }

        // Attempt to notify the registrant that their request was received (non-enumerating).
        try {
            Mail::send('emails.registration_submitted', [], function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Registration Received - STB Inventory Portal');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send registration-submitted email: ' . $e->getMessage());
        }

        try {
            $pendingCount = User::where(function($q) {
                $q->whereNull('approvalstatus')
                  ->orWhere('approvalstatus', '');
            })->count();

            $subject = $pendingCount . ' Pending for approval account';

            Mail::send('emails.pending_registration', ['pendingCount' => $pendingCount], function ($message) use ($subject) {
                $message->to('jpzamora@dswd.gov.ph')
                        ->subject('STB Inventory Portal - ' . $subject);
            });
        } catch (\Exception $mailEx) {
            Log::error('Failed sending new registration notification: ' . $mailEx->getMessage());
        }

        $this->ensureMinResponseTime($start, 700);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Please wait for admin approval.',
            ]);
        }
        return redirect()->route('main')->with('success', 'Registration successful. Please wait for admin approval.');


    }
    public function login(Request $request){
        try {
            $IncomingFields = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $start = microtime(true);
        $user = User::where('email', $IncomingFields['email'])->first();

        // track failed login attempts per email (short lived)
        $emailKey = 'login_attempts:' . sha1(strtolower(trim($IncomingFields['email'])));
        $attempts = (int) Cache::get($emailKey, 0);

        $ajaxError = function($field, $msg) use ($request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $msg,
                    'errors' => [ $field => [$msg] ]
                ], 422);
            }
            return back()->withErrors([
                $field => $msg,
            ], 'login')->onlyInput('email');
        };

        // If threshold reached, require reCAPTCHA and verify it here
        if ($attempts >= 3) {
            $recaptchaToken = $request->input('g-recaptcha-response');
            if (empty($recaptchaToken)) {
                // increment attempts slightly to keep consistent throttling
                Cache::put($emailKey, $attempts + 1, now()->addMinutes(15));
                return $ajaxError('g-recaptcha-response', 'Please complete the reCAPTCHA.');
            }

            try {
                $recaptchaSecret = config('services.recaptcha.secret');
                $post = http_build_query([
                    'secret' => $recaptchaSecret,
                    'response' => $recaptchaToken,
                    'remoteip' => $request->ip(),
                ]);
                $opts = [
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post) . "\r\n",
                        'content' => $post,
                        'timeout' => 5,
                    ],
                ];
                $context = stream_context_create($opts);
                $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
                if ($result === false) {
                    throw new \Exception('reCAPTCHA request failed');
                }
                $verify = json_decode($result, true);
            } catch (\Exception $e) {
                Cache::put($emailKey, $attempts + 1, now()->addMinutes(15));
                return $ajaxError('g-recaptcha-response', 'reCAPTCHA verification failed. Please try again.');
            }

            if (empty($verify) || empty($verify['success'])) {
                Cache::put($emailKey, $attempts + 1, now()->addMinutes(15));
                return $ajaxError('g-recaptcha-response', 'reCAPTCHA verification failed. Please try again.');
            }
        }

        $genericAuthMsg = 'Invalid email or password.';

        if (!$user) {
            $this->ensureMinResponseTime($start, 700);
            $curr = (int) Cache::get($emailKey, 0);
            Cache::put($emailKey, $curr + 1, now()->addMinutes(15));
            return $ajaxError('email', $genericAuthMsg);
        }

        if ($user->active == 0 || empty($user->approvalstatus) || is_null($user->approvalstatus) || $user->approvalstatus === 'R' || $user->approvalstatus !== 'A') {
            $this->ensureMinResponseTime($start, 700);
            $curr = (int) Cache::get($emailKey, 0);
            Cache::put($emailKey, $curr + 1, now()->addMinutes(15));
            return $ajaxError('email', $genericAuthMsg);
        }
        $remember = $request->boolean('remember');

        // Verify password without fully authenticating; require OTP as a second factor.
        if (!Hash::check($IncomingFields['password'], $user->password)) {
            $this->ensureMinResponseTime($start, 700);
            $curr = (int) Cache::get($emailKey, 0);
            Cache::put($emailKey, $curr + 1, now()->addMinutes(15));
            return $ajaxError('email', $genericAuthMsg);
        }

        // successful credential check -> reset failed attempts
        Cache::forget($emailKey);

        $request->session()->put('otp_user_id', $user->id);
        $request->session()->forget(['otp_code', 'otp_expires_at']);
        $request->session()->put('otp_attempts', 0);
        $request->session()->put('otp_sent', false);
        $request->session()->put('otp_send_count', $this->getOtpSendCount($request, $user->id));
        $lock = $this->getOtpLockPayload($request);
        if ($lock) {
            $request->session()->put('otp_send_locked_until', Carbon::parse($lock['locked_until'])->toDateTimeString());
        }

        $otpState = $this->otpSendStatePayload($request);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'otp_required' => true,
            ] + $otpState);
        }

        return redirect()->route('otp.form');
    }

    // Show OTP input form
    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('otp_user_id')) {
            return redirect()->route('landing');
        }

        $expiresAt = $request->session()->get('otp_expires_at');
        $parsedExpiry = null;
        if ($expiresAt) {
            try {
                $parsedExpiry = Carbon::parse($expiresAt);
            } catch (\Exception $e) {
                $parsedExpiry = null;
            }
        }

        return view('auth.otp_verify', $this->otpSendStatePayload($request, $parsedExpiry));
    }

    public function sendOtp(Request $request)
    {
        $userId = $request->session()->get('otp_user_id');
        if (!$userId) {
            return $this->otpJsonError($request, 'OTP session not found. Please login again.', 422);
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget([
                'otp_user_id',
                'otp_code',
                'otp_expires_at',
                'otp_attempts',
                'otp_sent',
                'otp_send_count',
                'otp_send_locked_until',
            ]);
            return $this->otpJsonError($request, 'Account not found.', 422);
        }

        $lock = $this->getOtpLockPayload($request);
        if ($lock) {
            return $this->otpJsonError($request, 'OTP requests are temporarily restricted. Please wait 5 minutes before trying again.', 429);
        }

        $sendCount = $this->getOtpSendCount($request, $user->id);
        if ($sendCount >= self::OTP_SEND_LIMIT) {
            $lockedUntil = Carbon::now()->addMinutes(self::OTP_SEND_LOCK_MINUTES);
            Cache::put($this->otpThrottleKey($user->id, 'locked_until'), $lockedUntil->toDateTimeString(), $lockedUntil);
            $request->session()->put('otp_send_locked_until', $lockedUntil->toDateTimeString());
            $this->putOtpSendCount($request, $user->id, $sendCount, $lockedUntil);
            return $this->otpJsonError($request, 'OTP requests are temporarily restricted. Please wait 5 minutes before trying again.', 429);
        }

        if (!$this->verifyRecaptchaToken($request, $request->input('g-recaptcha-response'))) {
            return $this->otpJsonError($request, 'Please complete the CAPTCHA before sending an OTP.', 422, [
                'g-recaptcha-response' => ['Please complete the CAPTCHA before sending an OTP.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'A verification code was sent to your email.',
        ] + $this->sendOtpForPendingLogin($request, $user));
    }

    // Verify OTP and complete login
    public function verifyOtp(Request $request)
    {
        if ($this->getOtpLockPayload($request)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'OTP input is temporarily restricted. Please wait 5 minutes before trying again.',
                ] + $this->otpSendStatePayload($request), 429);
            }

            return redirect()->route('landing')->withErrors(['otp' => 'OTP input is temporarily restricted. Please wait 5 minutes before trying again.']);
        }

        $userId = $request->session()->get('otp_user_id');
        $code = $request->session()->get('otp_code');
        $expiresAt = $request->session()->get('otp_expires_at');
        $attempts = $this->getOtpVerifyAttempts($request);

        $request->session()->put('otp_attempts', $attempts);

        if (!$userId || !$code || !$expiresAt) {
            return redirect()->route('landing')->withErrors(['otp' => 'OTP session not found. Please login again.']);
        }

        if (Carbon::now()->gt(Carbon::parse($expiresAt))) {
            $request->session()->forget(['otp_user_id','otp_code','otp_expires_at','otp_attempts']);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The verification code has expired. Please login again.'], 422);
            }
            return redirect()->route('landing')->withErrors(['otp' => 'The verification code has expired. Please login again.']);
        }

        if ($attempts >= self::OTP_VERIFY_LIMIT) {
            $lockedUntil = Carbon::now()->addMinutes(self::OTP_SEND_LOCK_MINUTES);
            Cache::put($this->otpThrottleKey($userId, 'locked_until'), $lockedUntil->toDateTimeString(), $lockedUntil);
            $this->putOtpVerifyAttempts($request, (int) $userId, $attempts, $lockedUntil);
            $this->putOtpSendCount($request, (int) $userId, $this->getOtpSendCount($request, (int) $userId), $lockedUntil);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'OTP input is temporarily restricted. Please wait 5 minutes before trying again.',
                ] + $this->otpSendStatePayload($request), 429);
            }

            return redirect()->route('landing')->withErrors(['otp' => 'OTP input is temporarily restricted. Please wait 5 minutes before trying again.']);
        }

        $validated = $request->validate([
            'otp_code' => 'required|string',
        ]);

        if (hash_equals((string)$code, (string)$validated['otp_code'])) {
            $user = User::find($userId);
            if (!$user) {
                $request->session()->forget(['otp_user_id','otp_code','otp_expires_at','otp_attempts']);
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Account not found.'], 422);
                }
                return redirect()->route('landing')->withErrors(['otp' => 'Account not found.']);
            }
            Auth::loginUsingId($user->id, false);
            try {
                if (Schema::hasTable('userlogs')) {
                    DB::table('userlogs')->insert([
                        'user_id' => $user->id,
                        'action' => 'login',
                        'performed_by' => $user->id,
                        'meta' => json_encode(['ip' => $request->ip(), 'user_agent' => $request->userAgent()]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // best-effort logging; ignore failures
            }
            $request->session()->regenerate();
            $request->session()->forget(['otp_user_id','otp_code','otp_expires_at','otp_attempts']);
            $this->clearOtpThrottle($request, $user->id);
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => url('/main')]);
            }
            return redirect()->intended('main');
        }

        $nextAttempts = $attempts + 1;
        $this->putOtpVerifyAttempts($request, (int) $userId, $nextAttempts);

        if ($nextAttempts >= self::OTP_VERIFY_LIMIT) {
            $lockedUntil = Carbon::now()->addMinutes(self::OTP_SEND_LOCK_MINUTES);
            Cache::put($this->otpThrottleKey($userId, 'locked_until'), $lockedUntil->toDateTimeString(), $lockedUntil);
            $this->putOtpVerifyAttempts($request, (int) $userId, $nextAttempts, $lockedUntil);
            $this->putOtpSendCount($request, (int) $userId, $this->getOtpSendCount($request, (int) $userId), $lockedUntil);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'OTP input is temporarily restricted. Please wait 5 minutes before trying again.',
                ] + $this->otpSendStatePayload($request), 429);
            }

            return redirect()->route('landing')->withErrors(['otp' => 'OTP input is temporarily restricted. Please wait 5 minutes before trying again.']);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'The code is incorrect.'], 422);
        }
        return back()->withErrors(['otp_code' => 'The code is incorrect.'])->withInput();
    }

    // Resend OTP
    public function resendOtp(Request $request)
    {
        return $this->sendOtp($request);
    }

    public function logout(Request $request)
    {
        $uid = Auth::id();
        if ($uid && Schema::hasTable('userlogs')) {
            try {
                DB::table('userlogs')->insert([
                    'user_id' => $uid,
                    'action' => 'logout',
                    'performed_by' => $uid,
                    'meta' => json_encode(['ip' => $request->ip(), 'user_agent' => $request->userAgent()]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // best-effort logging
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('main');
    }

    public function profile(){
        return redirect()->route('main')->with('profile_modal_open', true);
    }

    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        $normalize = static fn ($value) => trim((string) ($value ?? ''));
        $detailsChanged = $normalize($request->email) !== $normalize($user->email)
            || $normalize($request->usergroup) !== $normalize($user->usergroup)
            || $normalize($request->phonenumber) !== $normalize($user->phonenumber)
            || $normalize($request->gender) !== $normalize($user->gender)
            || $normalize($request->address) !== $normalize($user->address)
            || $request->filled('new_password');
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'usergroup' => 'required|in:admin,user,sysadmin',
            'current_password' => $detailsChanged ? 'required' : 'nullable',
            'new_password' => [
                'nullable',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
                'confirmed',
            ],
            'phonenumber' => ['nullable', 'string', 'max:20', new NoMarkup()],
            'gender' => 'nullable|string|in:Male,Female',
            'address' => ['nullable', 'string', 'max:500', new NoMarkup()],
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'new_password.regex' => 'New password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'profileUpdate')
                ->with('profile_modal_open', true)
                ->withInput();
        }

        if ($detailsChanged && !password_verify($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('profile_error', 'Current password is incorrect.')
                ->with('profile_modal_open', true)
                ->withInput();
        }

        try {
            $updateData = [
                'email' => $request->email,
                'usergroup' => $request->usergroup,
                'phonenumber' => $request->phonenumber,
                'gender' => $request->gender,
                'address' => $request->address,
            ];

            if ($request->filled('new_password')) {
                $updateData['password'] = bcrypt($request->new_password);
            }

            if ($request->hasFile('profile_picture')) {
                $newProfilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');

                if ($user->profile_picture_path) {
                    Storage::disk('public')->delete($user->profile_picture_path);
                }

                $updateData['profile_picture_path'] = $newProfilePicturePath;
            }

            $user->update($updateData);

            if (Schema::hasTable('userlogs')) {
                try {
                    DB::table('userlogs')->insert([
                        'user_id' => $user->id,
                        'action' => 'update',
                        'performed_by' => Auth::check() ? (Auth::user()->id ?? Auth::id()) : null,
                        'meta' => json_encode($updateData),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // best-effort logging
                }
            }

            return redirect()->back()
                ->with('profile_success', 'Profile updated successfully.')
                ->with('profile_modal_open', true);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('profile_error', 'Failed to update profile: ' . $e->getMessage())
                ->with('profile_modal_open', true)
                ->withInput();
        }
    }


    public function index()
    {
        $users = User::where('approvalstatus', 'A')->orderBy('created_at', 'asc')->get();
        return view('admin.users', compact('users'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255', new NoMarkup()],
            'middlename' => ['nullable', 'string', 'max:255', new NoMarkup()],
            'lastname' => ['required', 'string', 'max:255', new NoMarkup()],
            'email' => 'required|email|unique:users,email,' . $user->id,
            'usergroup' => 'required|in:admin,user,sysadmin',
            'active' => 'required|boolean',
            'password' => [
                'nullable',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
                'confirmed',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $updateData = [
                'firstname' => $request->firstname,
                'middlename' => $request->middlename,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'usergroup' => $request->usergroup,
                'active' => $request->active,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $user->update($updateData);

            if (Schema::hasTable('userlogs')) {
                try {
                    DB::table('userlogs')->insert([
                        'user_id' => $user->id,
                        'action' => 'profile_update',
                        'performed_by' => Auth::check() ? (Auth::user()->id ?? Auth::id()) : null,
                        'meta' => json_encode($updateData),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                }
            }

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user. Please try again.')
                ->withInput();
        }
    }
}
