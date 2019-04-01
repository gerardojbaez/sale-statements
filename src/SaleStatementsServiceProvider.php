<?php

namespace Gerardojbaez\SaleStatements;

use Illuminate\Support\ServiceProvider;

class SaleStatementsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/sale-statements.php' => config_path('sale-statements.php'),
        ], 'sale-statements-config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDefaultConfigurations();
    }

    /**
     * Register package's default configuration.
     *
     * @return void
     */
    public function registerDefaultConfigurations()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sale-statements.php', 'sale-statements'
        );
    }
}
