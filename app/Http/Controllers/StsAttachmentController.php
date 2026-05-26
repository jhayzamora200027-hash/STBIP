<?php

namespace App\Http\Controllers;

use App\Models\StsAttachment;
use App\Rules\NoMarkup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StsAttachmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'region' => ['required', 'string', 'max:255', new NoMarkup()],
            'province' => ['nullable', 'string', 'max:255', new NoMarkup()],
            'municipality' => ['nullable', 'string', 'max:255', new NoMarkup()],
            'title' => ['required', 'string', 'max:1024', new NoMarkup()],
            'year_of_moa' => ['nullable', 'string', 'max:50', new NoMarkup()],
            'attachment' => 'nullable|file|mimes:pdf|max:30720',
            'attachments' => 'nullable|array|min:1',
            'attachments.*' => 'file|mimes:pdf|max:30720',
        ]);

        $files = $this->extractAttachmentFiles($request);
        if ($files === []) {
            return $this->attachmentValidationErrorResponse($request);
        }

        $maxBytes = 30 * 1024 * 1024; // 30MB in bytes
        foreach ($files as $file) {
            if ($file->getSize() > $maxBytes) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Uploaded file exceeds the maximum allowed size of 30MB.'
                    ], 413);
                }

                return redirect()->back()->withErrors(['attachments' => 'Uploaded file exceeds the maximum allowed size of 30MB.']);
            }
        }

        $createdBy = Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null;
        $attachments = [];
        foreach ($files as $file) {
            $storedPath = $file->store('st_attachments', 'public');

            $attachmentModel = StsAttachment::create([
                'region' => $validated['region'],
                'province' => $validated['province'] ?? null,
                'municipality' => $validated['municipality'] ?? null,
                'title' => $validated['title'],
                'year_of_moa' => $validated['year_of_moa'] ?? null,
                'file_path' => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'created_by' => $createdBy,
                'action' => 'added',
            ]);

            $attachments[] = [
                'id' => $attachmentModel->id,
                'title' => $attachmentModel->title,
                'original_filename' => $attachmentModel->original_filename,
                'file_size' => $attachmentModel->file_size,
                'url' => route('sts.attachments.show', $attachmentModel),
            ];
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($attachments) > 1 ? 'Attachments uploaded successfully.' : 'Attachment uploaded successfully.',
                'attachment' => $attachments[0],
                'attachments' => $attachments,
                'uploader' => Auth::user()?->name,
            ]);
        }

        return redirect()->back()->with('success', count($attachments) > 1 ? 'Attachments uploaded successfully.' : 'Attachment uploaded successfully.');
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function extractAttachmentFiles(Request $request): array
    {
        $files = $request->file('attachments', []);
        if (!is_array($files)) {
            $files = $files ? [$files] : [];
        }

        $singleFile = $request->file('attachment');
        if ($singleFile instanceof UploadedFile) {
            $files[] = $singleFile;
        }

        return array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));
    }

    private function attachmentValidationErrorResponse(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $message = 'Select at least one PDF attachment.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => [
                    'attachments' => [$message],
                ],
            ], 422);
        }

        return redirect()->back()->withErrors(['attachments' => $message]);
    }

    public function show(StsAttachment $attachment)
    {
        
        if ($attachment->action !== 'added') {
            abort(404);
        }

        if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        
        $absolutePath = Storage::disk('public')->path($attachment->file_path);

        $response = response()->file($absolutePath, [
            'Content-Type' => $attachment->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($attachment->original_filename ?: basename($absolutePath)) . '"',
        ]);

        // Override global CSP frame-ancestors for this response so the PDF can be embedded in our UI iframe.
        // Keep other CSP directives similar to the default policy but allow framing by same origin.
        $csp = "default-src 'self'; script-src 'self' https: 'unsafe-inline'; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https: data:; object-src 'none'; base-uri 'self'; frame-ancestors 'self';";
        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }

    public function destroy(StsAttachment $attachment)
    {
        
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        
        
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

        // If AJAX request, return JSON so client can update UI without reload
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }

    public function logs(Request $request)
    {
        
        \Illuminate\Support\Facades\Log::debug('STsAttachmentController@logs called', [
            'auth_check' => Auth::check(),
            'user' => Auth::user()
                    ? [
                        'id' => Auth::user()->id ?? null,
                        'user_id' => Auth::user()->user_id ?? null,
                        'usergroup' => Auth::user()->usergroup ?? null,
                        'name' => Auth::user()->name ?? null,
                    ]
                : null,
            'session_id' => session()->getId(),
            'cookies' => $request->cookies->all(),
        ]);

        
        
        
        

        
        $query = StsAttachment::orderBy('created_at', 'desc');
        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        
        $logs = $query->paginate(15);

        if ($request->ajax()) {
            $html = view('dashboard.maincomponents.partials.stsattachment_logs', compact('logs'))->render();
            return response()->json(['html' => $html]);
        }

        
        return view('dashboard.maincomponents.stsattachment_logs', compact('logs'));
    }
}
