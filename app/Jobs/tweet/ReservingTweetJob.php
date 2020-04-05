<?php
//self:dispatch,jobループdispatchを使用して、
//ターゲットのフォロワー取得と「フレンドシップ確認と更新」「最新ツイート確認」を行なっています。
//各種処理の前に、api_request上限に達しているか？判定あり。
//tw_accoutテーブルのカラムで判定しています。

//下記、２種類のループ処理があります。外部ループ(ペジネーション)→内部ループ100回
//外部ループ「ターゲット100件取得」
//内部ループ「ターゲットを一件ずつ処理するループ」


//init: 初期準備　failed_queue準備と100件取得
//1.最新Tweetを一件ずつ取得しレコード更新 users_show
//2.次の対象に移る : 最新ツイートを取得　：　self::dispatch
//3.最新フレンドシップ取得し,レコード更新 friendships_lookup
//4.アンフォロー判定　：　この時点で最新ツイート状況は100件更新済み
//5.アンフォローjobへ以降
//6. 次のページがないなら(100未満のリスト)ならここで終了
//7.self::dispatch


// checkApiRestrictionAndStatus()　： リクエスト上限・起動状態を確認し、問題があればself::dispatch()


namespace App\Jobs\tweet;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use App\Domain\Services\ApiService\Tw\AdminApiRequest;
use App\Domain\Services\ApiService\Tw\CheckApiRequest2;

use App\Tw_Auto_Tweet;
use Log;
use App\Facades\ErrorService;

class ReservingTweetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AdminApiRequest, CheckApiRequest2;
    public $dto;
    public $options;
    public $queue_name = 'statuses_update';
    public $auto_function_name = "tweet";
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
    //自分のtwitterアカウントで紐つけ
    public function tags()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    
    public function handle()
    {

        Log::debug("roop");
        Log::debug("FriendshipRoopJob");

        try {
            $tweet = $this->getRealTime_Tweet();

            //削除依頼がある場合
            if ($tweet->tweet_status == 5) {
                $tweet->delete();
                $tweet->save();
                $this->job->delete();
                return;
            }
            
        //init: 初期準備
            $queue_id = $this->job->getJobId();
            $this->dto['queue_id'] =  $queue_id;//faile_jobテーブルのインサートで使用
            Log::debug("queu_id : ".$queue_id);

            //tweetデータ更新
          
            $tweet->queue_id = $queue_id;
            $tweet->tweet_status = 1;
            $tweet->save();

        //1.Tweet投稿
            $result = $this->accessTwitterAPI(
                $this->queue_name,
                $this->dto['my_tw_account'],
                "POST",
                $this->dto['request_url'],
                $this->dto['params']
            );

            //エラー確認
            if ($this->checkApiRestrictionAndStatus2(
                    $result, $tweet, $this->dto, 
                    $this->dto['api_requst']
                ) == true) 
            {
                $this->delete();
                return;
            };

            //投稿済み
            Log::debug('finish: 投稿しました');
            $tweet = $this->getRealTime_Tweet();
            $tweet->tweet_status = 4;
            $tweet->save();
            $this->finishAutoFunc($this->auto_function_name, $this->dto['my_tw_account']);

        } catch (\Exception $e) {
            $tweet = $this->getRealTime_Tweet();
            $tweet->tweet_status = 0;
            $tweet->save();
            Log::debug($e);
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
            Log::debug("エラー発生：予期せぬエラーが起きました。");
        }
    }
    // end handle
    public function getRealTime_Tweet() {
        return Tw_Auto_Tweet::find($this->dto['tweet_id']);
    }
}