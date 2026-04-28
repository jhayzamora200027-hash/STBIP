<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // ...existing Laravel web middleware...
            \App\Http\Middleware\ForceHttps::class,
            \App\Http\Middleware\EncryptCookies::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
               \App\Http\Middleware\SessionTimeout::class,
            // ...other middleware...
        ],

        'api' => [
            'throttle:api',
            \App\Http\Middleware\SecurityHeaders::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // ...existing...
        'auth' => \App\Http\Middleware\Authenticate::class,
        'sysadmin' => \App\Http\Middleware\SysAdminMiddleware::class,
        'admin' => \App\Http\Middleware\RequireAdminOrSysadmin::class,
        // ...other middleware...
    ];
}
