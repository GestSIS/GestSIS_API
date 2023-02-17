<?php

namespace App\Application\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Domaine\API\ImputationService;
use App\Domaine\API\ExerciceComptableService;
use App\Domaine\API\InterventionService;
use App\Domaine\API\ExerciceService;
use App\Domaine\API\SapeurService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('ImputationService', function ($app) {
            return new ImputationService();
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
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
