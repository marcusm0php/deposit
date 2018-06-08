<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;

class SmsProvider extends ServiceProvider
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
        $this->app->singleton(EasySms::class, function ($app) {
            return new EasySms(config('sms'));
        });

        $this->app->alias(EasySms::class, 'easysms');
    }
}
