<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    public function register(Request $request)
    {
        $emailForUserId = $request->email;
        $generatedUserId = null;
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
        
        if ($approvedEmailExists) {
            $error = ['email' => ['This email is already registered and approved.']];
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $error,
                ], 422);
            }
            return redirect()->back()
                ->withErrors($error, 'register')
                ->withInput();
        }
        
        if ($approvedUserIdExists) {
            $error = ['user_id' => ['This User ID is already registered and approved.']];
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $error,
                ], 422);
            }
            return redirect()->back()
                ->withErrors($error, 'register')
                ->withInput();
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

        if ($pendingEmail) {
            $error = ['email' => ['This email already has a pending registration. Please wait for admin review or contact support.']];
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $error,
                ], 422);
            }
            return redirect()->back()
                ->withErrors($error, 'register')
                ->withInput();
        }

        if ($pendingUserId) {
            $error = ['user_id' => ['This User ID already has a pending registration. Please wait for admin review or contact support.']];
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $error,
                ], 422);
            }
            return redirect()->back()
                ->withErrors($error, 'register')
                ->withInput();
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
        
        try {
            $IncomingFields = $validator->validated();
            $IncomingFields['password'] = bcrypt($IncomingFields['password']);
            $IncomingFields['active'] = 1; 
            $IncomingFields['name'] = trim($IncomingFields['firstname'] . ' ' . ($IncomingFields['middlename'] ?? '') . ' ' . $IncomingFields['lastname']);
            $IncomingFields['name'] = preg_replace('/\s+/', ' ', $IncomingFields['name']); // Clean up extra spaces
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

        $user = User::where('email', $IncomingFields['email'])->first();

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

        if (!$user) {
            return $ajaxError('email', 'No account found with this email address.');
        }

        if ($user->active == 0) {
            return $ajaxError('email', 'Your account is not active. Please contact support.');
        }

        if (empty($user->approvalstatus) || is_null($user->approvalstatus)) {
            return $ajaxError('email', 'Your registration is pending admin approval. Please wait for approval.');
        }

        if ($user->approvalstatus === 'R') {
            return $ajaxError('email', 'Your registration has been rejected. Please contact support.');
        }

        if ($user->approvalstatus !== 'A') {
            return $ajaxError('email', 'Your account access is restricted. Please contact support.');
        }

        if (Auth::attempt($IncomingFields)) {
            $request->session()->regenerate();
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => url('main')]);
            }
            return redirect()->intended('main');
        } else {
            return $ajaxError('password', 'The password is incorrect.');
        }
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
        
        // Validate the request
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
            // Prepare update data
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

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user. Please try again.')
                ->withInput();
        }
    }
}
