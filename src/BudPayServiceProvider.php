<?php

namespace BudPay;

use Illuminate\Support\ServiceProvider;

class BudPayServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the BudPay service as a singleton
        $this->app->singleton(BudPayService::class, function ($app) {
            return new BudPayService();
        });

        // Merge configuration from budpay.php into the application's config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/budpay.php', 'budpay'
        );
    }

    public function boot()
    {
        // Publish the configuration file to allow customization
        $this->publishes([
            __DIR__ . '/../config/budpay.php' => config_path('budpay.php'),
        ], 'config');
    }
}
