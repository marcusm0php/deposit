<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class GearApiInterfaceProvider extends ServiceProvider
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
        $this->app->singleton('cib', function ($app) {
            return new CibInterface(env('CIB_XX', null));
        });
    }
}
