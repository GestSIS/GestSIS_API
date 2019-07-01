<?php

namespace App\Providers;

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
        $this->app->singleton('ComptabiliteService', function ($app) {
            return new ComptabiliteService();
        });
        $this->app->singleton('InterventionService', function ($app) {
            return new InterventionService();
        });
        $this->app->singleton('ExerciceService', function ($app) {
            return new ExerciceService();
        });
        $this->app->singleton('SapeurService', function ($app) {
            return new SapeurService();
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
