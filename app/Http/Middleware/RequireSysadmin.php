<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireSysadmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || $user->usergroup !== 'sysadmin') {
            abort(403, 'Unauthorized: Sysadmin access required.');
        }
        return $next($request);
    }
}
