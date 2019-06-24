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
            'App\Contracts\EcritureRepository',
            'App\Repositories\EcritureRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\ExerciceRepository',
            'App\Repositories\ExerciceRepositoryEloquent'
        );
        $this->app->bind(
            'App\Contracts\IndemniteExerciceTypeRepository',
            'App\Repositories\IndemniteExerciceTypeRepositoryEloquent'
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
