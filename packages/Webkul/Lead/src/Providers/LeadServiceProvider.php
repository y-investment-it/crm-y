<?php

namespace Webkul\Lead\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class LeadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register() {}
}
