<?php


//init: 初期準備　failed_queue準備と100件取得
//1.Tweet100件取得
//2.いいねdispatch favorite_create

// checkApiRestrictionAndStatus()　： リクエスト上限・起動状態を確認し、問題があればself::dispatch()

namespace App\Jobs\favorite;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use App\Domain\Services\ApiService\Tw\AdminApiRequest;
use App\Domain\Services\ApiService\Tw\CheckApiRequest;
use Log;
use App\Facades\ErrorService;
use App\Jobs\favorite\FavoriteCreateRoopJob;

class SearchTweetRoopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AdminApiRequest, CheckApiRequest;
    public $dto;
    public $options;
    public $queue_name = "search_tweets";
    public $queue_name2 = "favorites_create";
    public $auto_function_name = "favorite";
    /**
     * Create a new job instance.
     *
     * @return void
     */
    // public function __construct()
    public function __construct(
        $dto, 
        $options = ['restart_flag'=>false]
        )
    {
        $this->dto = $dto;
        $this->options = $options;

    }  

    /**
     * Execute the job.
     *
     * @return void
     */

    
    public function handle()
    {
        Log::debug("roop");
        Log::debug("SearchTweetRoopJob");

        try {

            //削除依頼対応
            $my_account = $this->getRealTime_Accunt();
            if ($my_account->getAutoFunc($this->auto_function_name) == 5) {
                $this->initApi_Request();
                $this->job->delete();
                return;
            }
            if ($this->options['restart_flag'] == true) {
                Log::debug('Job再開：SearchTweetRoopJob');
                $my_account = $this->getRealTime_Accunt();
                $my_account->setApi_status('search_tweets', 1);//再開中から起動中に復帰
                $my_account->save();
            }
    
        //init: 初期準備
            $queue_id = $this->job->getJobId();
            $this->dto['queue_id'] =  $queue_id;//faile_jobテーブルのインサートで使用
            Log::debug("queu_id : ".$queue_id);
            $my_account = $this->getRealTime_Accunt();
            $my_account->setQueue_Id($this->auto_function_name, $queue_id);//再開する時、job_idを指すため
            $my_account->save();
          
        //1.Tweet100件取得
            $result = $this->accessTwitterAPI(
                "search_tweets",
                $this->dto['my_tw_account'],
                $this->dto['request_method'], 
                $this->dto['request_url'],
                $this->dto['params']
            );


            //APIリクエストの上限と状態を確認
            if ($this->checkApiRestrictionAndStatus(
                    $result, $this->dto, 
                    $this->queue_name,
                    ['restart_flag'=>true])
                == true) 
            {
                $this->delete();
                return;
            };

            //0件なら終わり
            // Log::debug(json_encode($result));
            // Log::debug(count($result->statuses));
            // $this->initApi_Request();
            // $this->job->delete();
            // return;
            if ( $result == null || count($result->statuses) == 0) {
                $this->finishAutoFunc($this->auto_function_name, $this->dto['my_tw_account']);
                return;
            }
        
            //次のページのカーソルを取得
            Log::debug("ツイート数：".count($result->statuses));
            Log::debug("next id：".$result->search_metadata->next_results);

            //次のtweetサーチ必要有無を判定
            Log::debug('env : '.config('api.TW_GET_COUNT'));
            if (count($result->statuses) < config('api.TW_GET_COUNT')) {
                Log::debug('次のサーチはなし');
                $this->dto['roop'] = false;

            } else {
                Log::debug('次のサーチはあり');
                $this->dto['params']['max_id'] = end($result->statuses)->id - 1;
                Log::debug('next max_id : '.$this->dto['params']['max_id']);
            }

        //2.いいね favorite_create

            //ツイートいいね対象作成
            $tweet_list = [];
            foreach ($result->statuses as $item) {
                array_push($tweet_list, $item->id);
            }
            $this->setFinishAndStartForApiStatus($this->queue_name, $this->queue_name2);
            FavoriteCreateRoopJob::dispatch($this->dto, $tweet_list)
            ->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE2')));
            $this->delete();

            return;
               
        } catch (\Exception $e) {
            $this->initApi_Request();
            Log::debug($e);
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
            Log::debug("エラー発生：予期せぬエラーが起きました。");
        }
    }
}