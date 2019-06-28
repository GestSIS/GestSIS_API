<?php

namespace App\Providers;

use App\Services\FraisService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('FraisService', function ($app) {
            return new FraisService($app->make('EcritureRepository'));
        });
        $this->app->singleton('InterventionService', function ($app) {
            return new InterventionService(
                $app->make('InterventionRepository'),
                $app->make('InterventionBusiness')
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
