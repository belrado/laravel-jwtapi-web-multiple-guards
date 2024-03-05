<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\CommonService;
use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('common', function() {
            return new CommonService();
        });

        $this->app->bind('appAuth', function() {
            return new AuthService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
