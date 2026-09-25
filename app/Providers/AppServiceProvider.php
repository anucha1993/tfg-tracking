<?php

namespace App\Providers;

use App\Services\ZohoAuth;
use App\Services\ZohoCrm;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ZohoAuth::class, function () {
            $cfg = config('services.zoho');

            return new ZohoAuth(
                $cfg['client_id'],
                $cfg['client_secret'],
                $cfg['refresh_token'],
                $cfg['accounts_url'],
            );
        });

        $this->app->singleton(ZohoCrm::class, function ($app) {
            $cfg = config('services.zoho');

            return new ZohoCrm(
                $app->make(ZohoAuth::class),
                $cfg['api_domain'],
                $cfg['api_version'],
                (int) config('tracking.cache_ttl_seconds', 60),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
