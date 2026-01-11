<?php

namespace App\Application\Providers;

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
            'App\Domaine\SPI\SapeurRepository',
            'App\Infrastructure\Repositories\SapeurRepositoryEloquent'
        );
        $this->app->bind(
            'App\Domaine\SPI\ExerciceRepository',
            'App\Infrastructure\Repositories\ExerciceRepositoryEloquent'
        );
        $this->app->bind(
            'App\Domaine\SPI\InterventionRepository',
            'App\Infrastructure\Repositories\InterventionRepositoryEloquent'
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
