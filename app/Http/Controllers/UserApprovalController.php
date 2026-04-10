<?php

namespace App\Http\Controllers;

use App\Models\ApprovalHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserApprovalController extends Controller
{

    public function index(Request $request)
    {
        if (!in_array(Auth::user()->usergroup, ['admin', 'sysadmin'])) {
            return redirect()->route('main')->with('error', 'You do not have permission to access this page.');
        }

        $pendingUsers = User::whereNull('approvalstatus')
            ->orWhere('approvalstatus', '')
            ->orderBy('created_at', 'desc')
            ->get();

        $historyEmail = trim((string) $request->query('history_email', ''));
        $historyDateFrom = trim((string) $request->query('history_date_from', ''));
        $historyDateTo = trim((string) $request->query('history_date_to', ''));

        $approvalHistoryQuery = ApprovalHistory::query()->latest('created_at');

        if ($historyEmail !== '') {
            $approvalHistoryQuery->where('applicant_email', 'like', '%' . $historyEmail . '%');
        }

        $fromDate = $this->parseHistoryDate($historyDateFrom);
        if ($fromDate) {
            $approvalHistoryQuery->where('created_at', '>=', $fromDate->startOfDay());
            $historyDateFrom = $fromDate->toDateString();
        } else {
            $historyDateFrom = '';
        }

        $toDate = $this->parseHistoryDate($historyDateTo);
        if ($toDate) {
            $approvalHistoryQuery->where('created_at', '<=', $toDate->endOfDay());
            $historyDateTo = $toDate->toDateString();
        } else {
            $historyDateTo = '';
        }

        $approvalHistoryLogs = $approvalHistoryQuery
            ->paginate(10, ['*'], 'history_page', max(1, (int) $request->query('history_page', 1)))
            ->appends(array_filter([
                'history_email' => $historyEmail !== '' ? $historyEmail : null,
                'history_date_from' => $historyDateFrom !== '' ? $historyDateFrom : null,
                'history_date_to' => $historyDateTo !== '' ? $historyDateTo : null,
            ]));

        return view('admin.approvals', compact('pendingUsers', 'approvalHistoryLogs', 'historyEmail', 'historyDateFrom', 'historyDateTo'));
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
        $reviewer = Auth::user();
        $reviewerName = $reviewer?->name;
        $reviewerEmail = $reviewer?->email;

        DB::transaction(function () use ($request, $user, $reviewerName, $reviewerEmail) {
            $user->approvalstatus = $request->approval_status;
            if ($request->approval_status === 'A' && $request->has('usergroup')) {
                $user->usergroup = $request->usergroup;
                $user->approvalcomment = null;
            }
            if ($request->approval_status === 'R') {
                $user->approvalcomment = $request->approvalcomment;
            }
            $user->approvedby = $reviewerName;
            $user->save();

            ApprovalHistory::query()->create([
                'user_id' => $user->id,
                'applicant_name' => $user->name ?: $user->display_name,
                'applicant_email' => $user->email,
                'action' => $request->approval_status === 'A' ? 'approved' : 'rejected',
                'reviewed_by_name' => $reviewerName,
                'reviewed_by_email' => $reviewerEmail,
                'assigned_usergroup' => $request->approval_status === 'A' ? $request->usergroup : null,
                'rejection_reason' => $request->approval_status === 'R' ? $request->approvalcomment : null,
            ]);
        });


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

    private function parseHistoryDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
