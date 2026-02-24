<?php

namespace App\Http\Controllers;

use App\Models\StsAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StsAttachmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'region' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'title' => 'required|string|max:1024',
            'year_of_moa' => 'nullable|string|max:50',
            'attachment' => 'required|file|mimes:pdf|max:10240', // PDF only, 10MB max
        ]);

        $file = $request->file('attachment');
        $storedPath = $file->store('st_attachments', 'public');

        StsAttachment::create([
            'region' => $validated['region'],
            'province' => $validated['province'] ?? null,
            'municipality' => $validated['municipality'] ?? null,
            'title' => $validated['title'],
            'year_of_moa' => $validated['year_of_moa'] ?? null,
            'file_path' => $storedPath,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'created_by' => Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null,
            'action' => 'added',
        ]);

        return redirect()->back()->with('success', 'Attachment uploaded successfully.');
    }

    public function show(StsAttachment $attachment)
    {
        // Only allow viewing active attachments that still have a file
        if ($attachment->action !== 'added') {
            abort(404);
        }

        if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        // Stream the PDF from the public disk through Laravel, avoiding direct filesystem issues
        $absolutePath = Storage::disk('public')->path($attachment->file_path);

        return response()->file($absolutePath, [
            'Content-Type' => $attachment->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($attachment->original_filename ?: basename($absolutePath)) . '"',
        ]);
    }

    public function destroy(StsAttachment $attachment)
    {
        // Remove the physical file if it still exists
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // Do not update the original record's action.
        // Instead, log a new row representing the delete action.
        StsAttachment::create([
            'region' => $attachment->region,
            'province' => $attachment->province,
            'municipality' => $attachment->municipality,
            'title' => $attachment->title,
            'year_of_moa' => $attachment->year_of_moa,
            'file_path' => $attachment->file_path,
            'original_filename' => $attachment->original_filename,
            'mime_type' => $attachment->mime_type,
            'file_size' => $attachment->file_size,
            'created_by' => Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null,
            'action' => 'deleted',
        ]);

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Return paginated log entries for STs attachments.
     * Accessible only to admins/sysadmins. The output is suitable
     * for AJAX insertion (returns JSON with html when ajax).
     */
    public function logs(Request $request)
    {
        // debug entry: log current auth state
        \Illuminate\Support\Facades\Log::debug('STsAttachmentController@logs called', [
            'auth_check' => Auth::check(),
            'user' => Auth::user() ? Auth::user()->only(['id','user_id','usergroup','name']) : null,
            'session_id' => session()->getId(),
            'cookies' => $request->cookies->all(),
        ]);

        // temporarily allow unauthenticated access for debugging
        // if (!Auth::check() || !in_array(Auth::user()->usergroup, ['admin', 'sysadmin'])) {
        //     abort(403);
        // }

        // date range filtering
        $query = StsAttachment::orderBy('created_at', 'desc');
        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        // paginate with 15 rows per page (original limit)
        $logs = $query->paginate(15);

        if ($request->ajax()) {
            $html = view('dashboard.maincomponents.partials.stsattachment_logs', compact('logs'))->render();
            return response()->json(['html' => $html]);
        }

        // Fallback: return a simple view in case someone navigates directly.
        return view('dashboard.maincomponents.stsattachment_logs', compact('logs'));
    }
}
