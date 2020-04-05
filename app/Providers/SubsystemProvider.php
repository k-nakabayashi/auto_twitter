<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Subsystem\AdminSnsAccount\CreateTargetAccount;


class SubsystemProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        app()->singleton('App\Http\Domain\Subsystem\AdminSnsAccount\CreateTargetAccount', function ($app) {
            $obj = new CreateTargetAccount;
            return $obj;
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
