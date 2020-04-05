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
        app()->singleton(
            'CommonDAO',
            'App\Domain\Repogitories\RESTfulDAO\CommonDAO'
        );

        app()->singleton(
            'App\Domain\Repogitories\RESTfulDAO\CommonDAOInterface',
            'App\Domain\Repogitories\RESTfulDAO\CommonDAO'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
    }
}
