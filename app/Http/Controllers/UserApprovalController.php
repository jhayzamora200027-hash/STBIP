<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserApprovalController extends Controller
{

    public function index()
    {
        if (!in_array(Auth::user()->usergroup, ['admin', 'sysadmin'])) {
            return redirect()->route('main')->with('error', 'You do not have permission to access this page.');
        }

        $pendingUsers = User::whereNull('approvalstatus')
            ->orWhere('approvalstatus', '')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.approvals', compact('pendingUsers'));
    }

    public function updateApproval(Request $request, $id)
    {
        if (!in_array(Auth::user()->usergroup, ['admin', 'sysadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }


        $rules = [
            'approval_status' => 'required|in:A,R'
        ];
        if ($request->approval_status === 'R') {
            $rules['approvalcomment'] = 'required|string|min:3';
        }
        if ($request->approval_status === 'A') {
            $rules['usergroup'] = 'required|in:admin,user,sysadmin';
        }
        $request->validate($rules);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }


        $user->approvalstatus = $request->approval_status;
        if ($request->approval_status === 'A' && $request->has('usergroup')) {
            $user->usergroup = $request->usergroup;
        }
        if ($request->has('approvalcomment')) {
            $user->approvalcomment = $request->approvalcomment;
        }
        if ($request->has('approvedby')) {
            $user->approvedby = $request->approvedby;
        }
        $user->save();


        $statusText = $request->approval_status === 'A' ? 'approved' : 'rejected';
        $rejectionReason = $request->approvalcomment ?? null;
        
        try {
            if ($request->approval_status === 'A') {
                Mail::raw(
                    "Dear {$user->name},\n\n" .
                    "Good news! Your registration has been approved by the administrator.\n\n" .
                    "You can now log in to your account using your email and password.\n\n" .
                    "Email: {$user->email}\n" .
                    "User Group: {$user->usergroup}\n\n" .
                    "Thank you for registering with STB Inventory Portal!\n\n" .
                    "Best regards,\n" .
                    "STB Inventory Team",
                    function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject('Registration Approved - STB Inventory');
                    }
                );
            } else {
                $reasonText = $rejectionReason ? "Reason for rejection: {$rejectionReason}\n\n" : "";
                Mail::raw(
                    "Dear {$user->name},\n\n" .
                    "We regret to inform you that your registration has been rejected by the administrator.\n\n" .
                    $reasonText .
                    "If you believe this is an error or would like more information, please contact support.\n\n" .
                    "Email: " . Auth::user()->email . "\n\n" .
                    "Best regards,\n" .
                    "STB Inventory Team",
                    function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject('Registration Status - STB Inventory');
                    }
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send approval email: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => "User registration has been {$statusText} successfully."
        ]);
    }
}
