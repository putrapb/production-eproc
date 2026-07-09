<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // M-4 Security: Tambahkan security headers pada setiap response
        // Mencegah: MIME sniffing, clickjacking, referrer leakage
        $this->app->make(\Illuminate\Http\Request::class);

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
            ->pushMiddleware(\App\Http\Middleware\SecurityHeaders::class);
    }
}

