<?php

namespace App\Application\Providers;

use App\Domaine\Business\ImputationBusiness;
use App\Domaine\Business\InterventionBusiness;
use Illuminate\Support\ServiceProvider;

class BusinessServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ImputationBusiness', function ($app) {
            return new ImputationBusiness();
        });
        $this->app->singleton('InterventionBusiness', function ($app) {
            return new InterventionBusiness();
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
