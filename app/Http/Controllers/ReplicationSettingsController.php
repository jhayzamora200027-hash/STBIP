<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ReplicationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplicationSettingsController extends Controller
{
    public function update(Request $request)
    {
        $redirectUrl = trim((string) $request->input('replication_redirect_url', ''));
        if ($redirectUrl !== '' && !preg_match('/^https?:\/\//i', $redirectUrl)) {
            $redirectUrl = 'https://' . $redirectUrl;
        }

        $request->merge(['replication_redirect_url' => $redirectUrl ?: null]);
        $validated = $request->validate([
            'replication_redirect_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'replication_redirect_url'],
            ['value' => $validated['replication_redirect_url'] ?? null]
        );

        return back()->with('status', 'Replication redirect destination updated.');
    }

    public function record(Request $request)
    {
        $validated = $request->validate([
            'social_technology_title' => ['required', 'string', 'max:255', 'exists:region_items,title'],
        ]);

        $redirectUrl = AppSetting::where('key', 'replication_redirect_url')->value('value');

        ReplicationRecord::create([
            'social_technology_title' => $validated['social_technology_title'],
            'user_id' => Auth::id(),
            'redirect_url' => $redirectUrl,
        ]);

        if (!$redirectUrl) {
            return back()->with('status', 'Replication request recorded. A replication destination has not been configured yet.');
        }

        return redirect()->away($redirectUrl);
    }
}