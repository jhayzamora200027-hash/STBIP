<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SysAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->usergroup !== 'sysadmin') {
            return redirect()
                ->route('main')
                ->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
