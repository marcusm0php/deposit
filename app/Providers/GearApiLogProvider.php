<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libs\GearApiLog;

class GearApiLogProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('galog', function ($app) {
            return new GearApiLog();
        });
    }
}
