<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // إجبار كل الروابط و الـ Assets على استخدام HTTPS في بيئة الإنتاج
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        Paginator::useBootstrapFive();

        RateLimiter::for('otp-verify', function (Request $request) {
            return Limit::perMinute(6)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('otp-resend', function (Request $request) {
            return Limit::perMinute(1)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}
