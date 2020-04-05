<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Services\ApiService\Tw\RequestToken;
use App\Domain\Services\TweetService\TweetStart;

use Log;
class MyServiceProvider extends ServiceProvider
{
    
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        app()->singleton('ErrorService','App\Domain\Services\ErrorService');

        app()->singleton('App\Domain\Services\ApiService\Tw\RequestToken', function ($app) {
            $obj = new RequestToken;
            return $obj;
        });


        //自動機能用
        //domain: follow, unfollow, tweet, favorite,
        //domain2: favorite
        //pattern: start, stop, restart
        $request = app("Illuminate\Http\Request");

        if (!empty($request->input("pattern"))) {
            $pattern = ucwords($request->input("pattern"));

            if (!empty($request->input("domain"))) {
                $domain = ucwords($request->input("domain"));
                $autofucion = "App\Domain\Services\FollowService"."\\".$domain.$pattern;
               
                app()->singleton(
                    'App\Domain\Services\AutoFunctionInterface',
                    $autofucion
                );
                
            } else if (!empty($request->input("domain2"))) {
                $domain = ucwords($request->input("domain2"));
                $autofucion = "App\Domain\Services\FavoriteService"."\\".$domain.$pattern;

                app()->singleton(
                    'App\Domain\Services\AutoFunctionInterface',
                    $autofucion
                );
            } else if (!empty($request->input("domain3"))) {
                $domain = ucwords($request->input("domain3"));
                $autofucion = "App\Domain\Services\UnfollowService"."\\".$domain.$pattern;

                app()->singleton(
                    'App\Domain\Services\AutoFunctionInterface',
                    $autofucion
                );
            } else if (!empty($request->input("domain4"))) {
                $domain = ucwords($request->input("domain4"));
                $autofucion = "App\Domain\Services\TweetService"."\\".$domain.$pattern;

                app()->singleton(
                    'App\Domain\Services\AutoFunctionInterface',
                    $autofucion
                );
            }


        } else {
            app()->singleton(
                'App\Domain\Services\AutoFunctionInterface',
                'App\Domain\Services\FollowService\FollowStart'
            );
           
        }

        app()->singleton('App\Domain\Services\TweetService\TweetStart', function ($app) {
            $obj = new TweetStart;
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
