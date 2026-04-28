<?php

namespace App\Http\Controllers;

use App\Models\User;
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
                'firstname' => 'required|string|max:255',
                'middlename' => 'nullable|string|max:255',
                'lastname' => 'required|string|max:255',
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
                'firstname' => 'required|string|max:255',
                'middlename' => 'nullable|string|max:255',
                'lastname' => 'required|string|max:255',
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
                $message->to('jpscarullo@dswd.gov.ph')
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

        // Generate one-time 6-digit code and store in session (5 minute lifetime)
        try {
            $otp = random_int(100000, 999999);
        } catch (\Exception $e) {
            $otp = rand(100000, 999999);
        }

        $expires = Carbon::now()->addMinutes(5);
        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_code', (string) $otp);
        $request->session()->put('otp_expires_at', $expires->toDateTimeString());
        $request->session()->put('otp_attempts', 0);

        // Send OTP via email (plaintext for now)
        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email to ' . $user->email . ': ' . $e->getMessage());
        }

        // Prepare masked email and ISO expiry for AJAX flows
        $masked = null;
        if ($user && $user->email) {
            $parts = explode('@', $user->email);
            $local = $parts[0] ?? '';
            $domain = $parts[1] ?? '';
            if (strlen($local) <= 2) {
                $maskedLocal = substr($local, 0, 1) . '*';
            } else {
                $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local)-2)) . substr($local, -1);
            }
            $masked = $maskedLocal . ($domain ? '@' . $domain : '');
        }

        $isoExpiry = null;
        try {
            $isoExpiry = Carbon::parse($expires->toDateTimeString())->toIso8601String();
        } catch (\Exception $e) {
            $isoExpiry = $expires->toDateTimeString();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'otp_required' => true,
                'masked_email' => $masked,
                'otp_expires_at' => $isoExpiry,
            ]);
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
        // normalize expiry to ISO-8601 for reliable JS parsing
        if ($expiresAt) {
            try {
                $expiresAt = Carbon::parse($expiresAt)->toIso8601String();
            } catch (\Exception $e) {
                // leave as-is if parse fails
            }
        }
        $user = null;
        $masked = null;
        $userId = $request->session()->get('otp_user_id');
        if ($userId) {
            $user = User::find($userId);
            if ($user && $user->email) {
                $email = $user->email;
                // mask email for display (e.g. j***@dswd.gov.ph)
                $parts = explode('@', $email);
                $local = $parts[0] ?? '';
                $domain = $parts[1] ?? '';
                if (strlen($local) <= 2) {
                    $maskedLocal = substr($local, 0, 1) . '*';
                } else {
                    $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local)-2)) . substr($local, -1);
                }
                $masked = $maskedLocal . ($domain ? '@' . $domain : '');
            }
        }

        return view('auth.otp_verify', [
            'otp_expires_at' => $expiresAt,
            'masked_email' => $masked,
        ]);
    }

    // Verify OTP and complete login
    public function verifyOtp(Request $request)
    {
        $userId = $request->session()->get('otp_user_id');
        $code = $request->session()->get('otp_code');
        $expiresAt = $request->session()->get('otp_expires_at');
        $attempts = (int) $request->session()->get('otp_attempts', 0);

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

        if ($attempts >= 5) {
            $request->session()->forget(['otp_user_id','otp_code','otp_expires_at','otp_attempts']);
            return redirect()->route('landing')->withErrors(['otp' => 'Too many attempts. Please login again.']);
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
            $request->session()->regenerate();
            $request->session()->forget(['otp_user_id','otp_code','otp_expires_at','otp_attempts']);
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => url('/main')]);
            }
            return redirect()->intended('main');
        }

        $request->session()->put('otp_attempts', $attempts + 1);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'The code is incorrect.'], 422);
        }
        return back()->withErrors(['otp_code' => 'The code is incorrect.'])->withInput();
    }

    // Resend OTP
    public function resendOtp(Request $request)
    {
        $userId = $request->session()->get('otp_user_id');
        if (!$userId) {
            return redirect()->route('landing');
        }
        $user = User::find($userId);
        if (!$user) return redirect()->route('landing');

        try {
            $otp = random_int(100000, 999999);
        } catch (\Exception $e) {
            $otp = rand(100000, 999999);
        }
        $expires = Carbon::now()->addMinutes(5);
        $request->session()->put('otp_code', (string)$otp);
        $request->session()->put('otp_expires_at', $expires->toDateTimeString());
        $request->session()->put('otp_attempts', 0);

        try {
            Mail::to($user->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP email to ' . $user->email . ': ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'A new verification code was sent to your email.']);
        }
        return back()->with('status', 'A new verification code was sent to your email.');
    }

    public function logout(Request $request)
    {
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
            'phonenumber' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:Male,Female',
            'address' => 'nullable|string|max:500',
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
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
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
