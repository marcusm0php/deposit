<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libs\ApiLog;
use App\Libs\Curl;

class ApiLibsProvider extends ServiceProvider
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
        $this->app->singleton('apilog', function ($app) {
            return new ApiLog();
        });
        $this->app->singleton('curl', function ($app) {
            return new Curl();
        });
    }
}
