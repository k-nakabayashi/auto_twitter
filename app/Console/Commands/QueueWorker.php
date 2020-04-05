<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
class QueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    
    public $queue_name;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // $this->queue_name = $queue_name;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //

        $output = [];
        $path = realpath('/usr/bin/php');
        $artisan = base_path(). DIRECTORY_SEPARATOR. "artisan";
        $command = "queue:listen";
        // $result =exec("nohup {$path} {$artisan} {$command} --queue==default,tweet,follow, --tries=1 --sleep=10 --timeout=960 > /dev/null & echo $!", $output);
        // $result =exec("nohup {$path} {$artisan} {$command} --queue==unfollow,favorite, --tries=1 --sleep=10 --timeout=960 > /dev/null & echo $!", $output);
        $result =exec("{$artisan} {$command} --queue==default,tweet,follow,unfollow,favorite, --tries=1 --sleep=10 --timeout=960");

        return true;
    }
}
