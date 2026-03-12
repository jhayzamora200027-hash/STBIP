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
                Mail::send('emails.approval_success', ['user' => $user], function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Registration Approved - STB Inventory Portal');
                });
            } else {
                Mail::send('emails.approval_rejected', ['user' => $user, 'reason' => $rejectionReason, 'adminEmail' => Auth::user() ? Auth::user()->email : null], function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Registration Status - STB Inventory Portal');
                });
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
