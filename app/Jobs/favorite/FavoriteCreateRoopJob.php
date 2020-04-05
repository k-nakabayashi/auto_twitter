<?php
//init: 初期準備　failed_queue準備と100件取得

//1.いいねリクエスト　favorites_create
//2.次の「いいね」処理に移る
//3. 次のページがないならここで終了
//4.次のツイートサーチーへ移行,リターンディスパッチ
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
use App\Jobs\favorite\SearchTweetRoopJob;

class FavoriteCreateRoopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, 
    AdminApiRequest, CheckApiRequest;

    public $back_dto;
    public $dto;
    public $tweet_counter;//最新ツイート取得で使う
    public $options;
    public $queue_name = "favorites_create";
    public $queue_name2 = "search_tweets";
    public $auto_function_name = 'favorite';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    // $back_dto
    public function __construct(
        $back_dto,//SearchTweetRoopJobに戻すために使います
        $tweet_id_list,
        $tweet_counter = 0,//いいね対象のindex
        $options = ['restart_flag'=>false]
        )
    {
        $this->back_dto = $back_dto;
        $this->tweet_counter = $tweet_counter;
        $this->options = $options;

        $request_url = 'https://api.twitter.com/1.1/favorites/create.json';
        
        $this->dto = [
            "api_requst" => "favorites_create",
            "tweet_id_list" => $tweet_id_list, //pk: target_friend_idのリスト
            "my_tw_account"=> $back_dto['my_tw_account'],
            "request_method" => 'POST',
            "request_url"=>$request_url, 
            'queue_name' => 'favorites_create',
            'queue_id' => "",
        ];
        $this->queue_name_id = $this->dto['my_tw_account']->queue_name;
    }  

    /**
     * Execute the job.
     *
     * @return void
     */

    
    public function handle()
    {
        Log::debug("roop");
        Log::debug("FavoriteCreateRoopJob");

        try {
            
            //削除依頼対応
            $my_account = $this->getRealTime_Accunt();
            if ($my_account->getAutoFunc($this->auto_function_name) == 5) {
                $this->initApi_Request();
                $this->job->delete();
                return;
            }
        //init: 初期準備
            if ($this->options['restart_flag'] == true) {
                Log::debug('Job再開：FavoriteCreateRoopJob');
                
                $my_account->setApi_status($this->queue_name, 1);//再開中から起動中に復帰
                $my_account->save();
            }
            
            $queue_id = $this->job->getJobId();
            $this->dto['queue_id'] =  $queue_id;//faile_jobテーブルのインサートで使用
            Log::debug("queue_id : ".$queue_id);
            $my_account = $this->getRealTime_Accunt();
            $my_account->setQueue_Id($this->auto_function_name, $queue_id);//再開する時、job_idを指すため
            $my_account->save();


        //1.いいねリクエスト　favorites_create
            //対象ツイートを設定
            $params = array(
                "id" =>  $this->dto['tweet_id_list'][$this->tweet_counter],
            );
            
            //いいねリクエスト実行
            Log::debug('いいね対象カウンター：'.$this->tweet_counter);
            $result = $this->accessTwitterAPI(
                "favorites_create",
                $this->dto['my_tw_account'],
                $this->dto['request_method'], 
                $this->dto['request_url'],
                $params
            );

            //APIリクエストの上限と状態を確認
            if ($this->checkApiRestrictionAndStatus(
                    $result, $this->back_dto, 
                    $this->queue_name
                ) == true) 
            {
                $this->delete();
                return;
            };

        //2.次の「いいね」処理に移る
            if ($this->dispatchRestOfTarget()) {
                Log::debug('いいねJob発行:ループ開始');
                $this->delete();
                return;
            }

        //3. 次のページがないならここで終了
            if ($this->back_dto['roop'] == false) {
                $this->finishAutoFunc($this->auto_function_name, $my_account);
                $this->delete();
                return;
            }

        //4.次のツイートサーチーへ移行
            //次の100件の処理: max_idはすでに設定済み
            $this->setFinishAndStartForApiStatus($this->queue_name, $this->queue_name2);
            
            SearchTweetRoopJob::dispatch($this->back_dto)
            ->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
            $this->delete();
           
            return;
       
        //リターンディスパッチ
        

        } catch (\Exception $e) {
            $this->initApi_Request();
            Log::debug($e);
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
            Log::debug("エラー発生：予期せぬエラーが起きました。");
        }
    }
    // end handle

    //2.次のターゲットのアンフォロー処理に移る
    public function dispatchRestOfTarget() {
        Log::debug('next いいね：');
       
        $count = count($this->dto['tweet_id_list']);
        Log::debug('tweet_id_listの数 : '.$count);

        $this->tweet_counter++;
        Log::debug('next counte : '.$this->tweet_counter);
        if ($this->tweet_counter < $count) {      
            Log::debug('次のいいねへ。');
            
            self::dispatch(
                $this->back_dto,
                $this->dto['tweet_id_list'], 
                $this->tweet_counter
            )->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE2')));
        
            return true;
        
        } else {

            $this->tweet_counter = 0;
            return false;
        }
    }

}