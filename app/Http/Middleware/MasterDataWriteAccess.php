<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterDataWriteAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->usergroup, ['user', 'admin', 'sysadmin'], true)) {
            return redirect()
                ->route('main')
                ->with('error', 'You do not have permission to modify master data items.');
        }

        return $next($request);
    }
}