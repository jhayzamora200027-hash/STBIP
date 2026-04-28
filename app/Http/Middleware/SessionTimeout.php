<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SessionTimeout
{
    /**
     * Handle an incoming request and enforce idle and absolute session timeouts.
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $now = Carbon::now()->timestamp;

            $last = Session::get('last_activity_time');
            $created = Session::get('session_created_time');

            $maxIdleMinutes = (int) config('session.lifetime', 5);
            $maxIdle = $maxIdleMinutes * 60;

            $absoluteMinutes = (int) config('session.absolute_lifetime', 0);
            $absolute = $absoluteMinutes > 0 ? $absoluteMinutes * 60 : 0;

            if (!$last) {
                Session::put('last_activity_time', $now);
            } else {
                if ($now - $last > $maxIdle) {
                    Auth::logout();
                    Session::invalidate();
                    Session::regenerateToken();
                    return redirect()->route('login')->with('status', 'Your session expired due to inactivity.');
                }
                Session::put('last_activity_time', $now);
            }

            if (!$created) {
                Session::put('session_created_time', $now);
            } else {
                if ($absolute > 0 && ($now - $created > $absolute)) {
                    Auth::logout();
                    Session::invalidate();
                    Session::regenerateToken();
                    return redirect()->route('login')->with('status', 'Your session has reached its maximum lifetime.');
                }
            }
        }

        return $next($request);
    }
}
