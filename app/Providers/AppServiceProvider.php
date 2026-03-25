<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap styles for Laravel pagination links
        Paginator::useBootstrap();
       
        try {
            if (!app()->runningInConsole() && request() instanceof Request) {
                $current = request()->getSchemeAndHttpHost();
                URL::forceRootUrl($current);
                if (request()->isSecure()) {
                    URL::forceScheme('https');
                }
            }
        } catch (\Throwable $e) {
        }
    }
}
