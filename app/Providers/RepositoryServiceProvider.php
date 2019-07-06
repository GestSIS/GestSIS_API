<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Contracts\SapeurRepository',
            'App\Repositories\SapeurRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\EcritureRepository',
            'App\Repositories\EcritureRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\ExerciceRepository',
            'App\Repositories\ExerciceRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\InterventionRepository',
            'App\Repositories\InterventionRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\IndemniteTypeRepository',
            'App\Repositories\IndemniteTypeRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\FraisTypeRepository',
            'App\Repositories\FraisTypeRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\CompteRepository',
            'App\Repositories\CompteRepositoryEloquent'
        );
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
