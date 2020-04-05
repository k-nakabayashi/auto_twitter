<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Jobs\ReleaseApiRestrictionJob;
use App\Jobs\RestartApiJob;
use App\Jobs\StopApiJob;

use App\Jobs\UnfollowingRoopJob;
use App\Jobs\unfollow\TargetFriendRoopJob;

use App\Jobs\tweet\ReservingTweetJob;

use App\Jobs\follow\TargetFollowerRoopJob;
use App\Jobs\follow\FollowingRoopJob;

use App\Jobs\favorite\SearchTweetRoopJob;
use App\Jobs\favorite\FavoriteCreateRoopJob;

class MyJobProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $app = app();

        $app->bindMethod(TargetFollowerRoopJob::class.'@handle',
            function ($job, $app)
            {   
                return $job->handle();
            }
        );

        $app->bindMethod(FollowingRoopJob::class.'@handle',
            function ($job, $app)
            {   
                return $job->handle();
            }
        );
        
        $app->bindMethod(ReleaseApiRestrictionJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );
                         
        $app->bindMethod(RestartApiJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );

        $app->bindMethod(StopApiJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );

        $app->bindMethod(TargetFriendRoopJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );
        
        $app->bindMethod(UnfollowingRoopJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );
        $app->bindMethod(TargetFriendRoopJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );
        
        $app->bindMethod(ReservingTweetJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );

        $app->bindMethod(SearchTweetRoopJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
        );
        
        $app->bindMethod(FavoriteCreateRoopJob::class.'@handle',
        function ($job, $app)
            {   
                return $job->handle();
            }
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
