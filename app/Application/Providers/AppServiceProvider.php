<?php

namespace App\Application\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Domaine\API\ComptabiliteService;
use App\Domaine\API\ExerciceComptableService;
use App\Domaine\API\InterventionService;
use App\Domaine\API\ExerciceService;
use App\Domaine\API\SapeurService;

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
        $this->app->singleton('ExerciceComptableService', function ($app) {
            return new ExerciceComptableService();
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
