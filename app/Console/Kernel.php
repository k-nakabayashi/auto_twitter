<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('horizon:snapshot')->everyFiveMinutes();
        // $login_user = Auth::user();      
   
        $queue_names = "default";
        $queue_names = $queue_names
        .",tweet"
        .",restart_api_request"
        .",follow"
        .",unfollow"
        .",favorite"
        .",";

  
        $boot = "listen";
        
        $schedule->command('queue:'.$boot.' --queue='.$queue_names.' --tries=1 --sleep=10 --timeout=960');

        // $schedule->command('queue:listen --queue=followers_list1_0 --tries=3 --delay=60');
        // $schedule->command('horizon:snapshot')->everyFiveMinutes();
        // $schedule->command('queue:work --queue=follow_2 --tries=3');
                //  ->hourly();
                // php artisan queue:work --stop-when-empty --queue=favorite1_0 --tries=1 --sleep=10 --timeout=960;

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
