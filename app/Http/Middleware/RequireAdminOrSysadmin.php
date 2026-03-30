<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireAdminOrSysadmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->usergroup, ['admin', 'sysadmin'], true)) {
            abort(403, 'Unauthorized: Admin or Sysadmin access required.');
        }

        return $next($request);
    }
}
