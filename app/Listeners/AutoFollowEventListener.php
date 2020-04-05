<?php

namespace App\Listeners;

use App\Events\AutoFollowEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Domain\Services\ApiService\Tw\AdminApiRequest;
use Dotenv\Regex\Result;

class AutoFollowEventListener implements ShouldQueue
{
    use AdminApiRequest;
    public $delay;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  AutoFollowEvent  $event
     * @return void
     */
    public function handle(AutoFollowEvent $event)
    {
        return;
        //
        //1.TwitterAPIからターゲットのフォロワーを取得
        if (config("api.TW_ENV") == "API") {

            var_dump("イベントリスナー起動：自動フォロー");
            var_dump("フォロー対象".$event->dto['params']["user_id"]);
            $obj = $this->accessTwitterAPI(
                'friendships_create',
                $event->dto['my_tw_account'], 
                $event->dto['request_method'], 
                $event->dto['request_url'], 
                $event->dto['params']
            );
        }

    }
}
