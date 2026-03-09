<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterDataDeleteAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->usergroup, ['admin', 'sysadmin'], true)) {
            return redirect()
                ->route('main')
                ->with('error', 'You do not have permission to delete master data items.');
        }

        return $next($request);
    }
}