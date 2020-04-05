<?php
//アンフォロー機能

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


namespace App\Jobs\unfollow;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use App\Domain\Services\ApiService\Tw\AdminApiRequest;
use App\Domain\Services\ApiService\Tw\CheckApiRequest;

use App\Tw_Target_Friend;
use Log;
use App\Facades\ErrorService;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;


class TargetFriendRoopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AdminApiRequest, CheckApiRequest;
    public $dto;
    public $account_counter = 0;//最新ツイート取得で使う
    public $options;
    public $queue_name = "users_show";
    public $queue_name2 = "friendships_lookup";
    public $queue_name3 = "friendships_destroy";
    public $auto_function_name = "unfollow";
    /**
     * Create a new job instance.
     *
     * @return void
     */
    // public function __construct()
    public function __construct(
        $dto, 
        $account_counter = 0, 
        $options = ["pagenator"=>true, 'restart_flag'=>false]
        )
    {
        $this->dto = $dto;
        $this->account_counter = $account_counter;
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
        Log::debug("TargetFriendRoopJob");

        try {

            //削除依頼対応
            $my_account = $this->getRealTime_Accunt();
            if ($my_account->getAutoFunc($this->auto_function_name) == 5) {
                $this->initApi_Request();
                return;
            }
            
            if ($this->options['restart_flag'] == true) {
                Log::debug('Job再開：TargetFriendRoopJob');
                $my_account = $this->getRealTime_Accunt();
                $my_account->setApi_status('users_show', 1);//再開中から起動中に復帰
                $my_account->save();
            }

        //init: 初期準備
            $queue_id = $this->job->getJobId();
            $this->dto['queue_id'] =  $queue_id;//faile_jobテーブルのインサートで使用
            Log::debug("queu_id : ".$queue_id);
            $my_account = $this->getRealTime_Accunt();
            $my_account->setQueue_Id($this->auto_function_name, $queue_id);//再開する時、job_idを指すため
            $my_account->save();
 
            //対象100件を取得
            if($this->options['pagenator'] == true) {

                $this->excutePaginator();//$this->dto['next'] = $next;次ペジネーションの準備は完了してます
            }
  
        //1.最新Tweetを一件ずつ取得しレコード更新 users_show
            // //最新ツイート投稿日時    
            $params = array(
                "user_id" => $this->dto['target_list'][$this->account_counter]->target_friend_user_id,//target_listはexcutePaginator()で設定ずみ
                "include_entities" => false,
            );
            
            //最新ツイート取得
            $result1 = $this->accessTwitterAPI(
                "users_show",
                $this->dto['my_tw_account'],
                "GET",
                'https://api.twitter.com/1.1/users/show.json',
                $params
            );

            //APIリクエストの上限と状態を確認
            if ($this->checkApiRestrictionAndStatus(
                    $result1, $this->dto, 
                    $this->queue_name, 
                    ["pagenator"=>false, 'restart_flag'=>true])
                == true) 
            {
                $this->delete();
                return;
            };

            //最新ツイート時刻を更新
            $model = new Tw_Target_Friend();
            $target = $model->find($this->dto['target_list'][$this->account_counter]->target_friend_id);
            $this->saveLastTweet($result1, $target);
                    

        //2.最新フレンドシップ取得し,レコード更新 friendships_lookup
            $this->setFinishAndStartForApiStatus($this->queue_name, $this->queue_name2);

            //フレンドシップのリクエストクエリを準備            
            Log::debug('最新フレンドシップ取得開始');
            $result2 = $this->accessTwitterAPI(
                "friendships_lookup",
                $this->dto['my_tw_account'],
                "GET",
                'https://api.twitter.com/1.1/friendships/lookup.json',
                ["user_id" => $target->target_friend_user_id,]
            );

            if ($this->checkApiRestrictionAndStatus(
                    $result2, $this->dto, 
                    $this->queue_name2,
                    ["pagenator"=>false, 'restart_flag'=>true])
                == true) 
            {
                $this->delete();
                return;
            };

        //4.アンフォロー判定　：　この時点で最新ツイート状況更新済み
            //取得した結果と対象100件のから1件ずつ比較してアンフォロー対象を抽出
            // $unfollow_target_id_list = $this->chekUnfollowFlag($result2, $model);

            if ($this->chekUnfollowFlag($result2, $model)) {
                //セルフディスパッチ
                //今の対象がアンフォロー対象外。次の対象へ移行する
                if ($this->getRestOfTarget()) {
                    Log::debug('対象がアンフォロー対象外だったため、次のTargetFriendRoopJob発行');
                    $this->delete();
                    return;
                }
            }

            Log::debug('アンフォロー対象id 表示');
            Log::debug(json_encode($target->getKey()));
            

        //5.アンフォロー
            $result3 = $this->accessTwitterAPI(
                "friendships_destroy",
                $this->dto['my_tw_account'],
                'POST', 
                'https://api.twitter.com/1.1/friendships/destroy.json',
                ['user_id' => $target->target_friend_user_id]
            );

            if ($this->checkApiRestrictionAndStatus(
                $result3, $this->dto, 
                $this->queue_name3,
                ["pagenator"=>false, 'restart_flag'=>true])
                == true) 
            {
                $this->delete();
                return;
            };
            //次の対象へ移る
            if ($this->getRestOfTarget()) {
                Log::debug('次の対象へ移る　次のTargetFriendRoopJob発行');
                $this->delete();
                return;
            }
            
        //6. 次のページがないなら(100未満のリスト)ならここで終了
            Log::debug("nextpage");
            Log::debug(json_encode($this->dto['next']));
            if ($this->dto['next'] == null) {
                Log::debug('アンフォロー機能完了しました。');
                $this->finishAutoFunc($this->auto_function_name, $this->dto['my_tw_account']);
                $this->delete();
                return;
            }
        //第2陣： job終了
        ///////////////////////////////////////////////////////////////
        
        //7.次の100件を取得して,self::dispatch :  "friendships_lookup"のリクエストの時点で、statusは「起動中」
            //ここに到達するということは、アンフォローする対象がいない場合
            //次の100件の処理へ移行: 
            Log::debug('ペジネーションあり：次のTargetFriendRoopJob発行');
            self::dispatch($this->dto)//取得した100件からまたindex0から対象をスタートする
            ->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
            $this->delete();
           
            return;
       
        //第3陣： self::dispacth
        ///////////////////////////////////////////////////////////////
            
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        

        } catch (\Exception $e) {
           
            Log::debug($e);
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            $this->initApi_Request();
        }
    }
    // end handle

    //ペジネーショで次の100件数　：　次のself::dispatchの準備
    //next_pageを設定しています
    public function excutePaginator () {
        
        $tw_target_account_list = DB::table('tw__target__friends')
        ->where('tw_account_id', $this->dto['my_tw_account']->getKey())
        ->where('follow_at', "!=", null)
        ->where('deleted_at', null)
        ->paginate(config('api.TW_PAGE'), $this->dto['select_columns'], 'page', $this->dto['next']);

        $data_list = [];
        foreach ($tw_target_account_list->items() as $item){
            array_push($data_list, $item->target_friend_user_id);
        }

        $params = array(
            "user_id" => $data_list,
        );

        //次のページを取得
        $next = $tw_target_account_list->nextPageUrl();
        if ($next != null) {
          //nullなら次のページなし
          $next = (int)substr( $next, -1 );
        }

        //次のdispatch用にdtoを更新
        $this->dto['target_list'] = $tw_target_account_list->items();
        $this->dto['params'] = $params;
        $this->dto['next'] = $next;
    }

    public function saveLastTweet($result, $target) {

        if (isset($result->status)) {
            $last_tw_at = new Carbon($result->status->created_at);
            $target->last_tw_at = $last_tw_at->format("Y/m/d H:i:s");
            $target->save();
        }
    }

    //2.次の対象に移る : 最新ツイートを取得　：　self::dispatch
    public function getRestOfTarget () {

        $count = count($this->dto['target_list']);

        Log::debug('counter : '.$this->account_counter);
        Log::debug("count : $count");
        
        $this->account_counter++;

       
        if ($this->account_counter < $count) {
            
            //次の対象へ移る
            //ペジネーションは起動させない
            self::dispatch(
                $this->dto, $this->account_counter, 
                ["pagenator"=>false, 'restart_flag'=>false]
            )
            ->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
            
            $this->delete();
            return true;

        } else {
             //次の対象がないので、次ペジネータ起動するJob発行かもしくはアンフォロー機能完了します。
            $this->account_counter = 0;
            return false;
        }
    }

    //4.アンフォロー判定　：　この時点で最新ツイート状況は100件更新済み
    //trueを返すと、次のターゲットのデータをとる
    //falseを返すと、アンフォロー処理に移行します。
    public function chekUnfollowFlag($result, $model) {

        $account = $this->dto['target_list'][$this->account_counter];
        $item = $result[0];

        if ($account->target_friend_user_id == $item->id) {
            Log::debug('アンフォローチェック対象 : '.$account->target_friend_user_id);
            //関係なし
            if (in_array('none', $item->connections)) {
                Log::debug('関係なし');
                return true;
            }

            //リアルタイム判定なので新たに作成
            $target = $model->find($account->target_friend_id);//modelはtw_target_friend.
            
            //フォロー判定開始
            //異常系
            if (in_array('following', $item->connections)) {
                //ここまでくるとフォローしているが、フォローされていない

                //アンフォロー判定開始
                $now_date = new Carbon(date('Y/m/d H:i:s'));

                //1 follow_at
                //168時間＝7日
                $follow_at = new Carbon($target->follow_at);
                if (
                    !in_array('followed_by', $item->connections) && //フォローされておらず
                    ($now_date->diffInHours($follow_at) >= 168 && $now_date > $follow_at)) //制限時間オーバー
                {
                    Log::debug("アンフォロー：フォロー返しの経過オーバー");
                    return false;
                }

                //2. last_tw_at
                //360時間＝15日
                $last_tw_at = new Carbon($target->last_tw_at);
                if ($now_date->diffInHours($last_tw_at) >= 360 && $now_date > $last_tw_at) {
                    Log::debug("アンフォロー：ツイート更新不十分");
                    return false;
                }
            }

            //正常系
            //最新ツイートが15日以内でかつフォローされている場合
            if (in_array('followed_by', $item->connections)){
                //フォローされてれなければ「フォロー済み」に更新
                Log::debug("フォローされた");
                $target->followed_at = date('Y/m/d H:i:s');
                $target->save();
                return true;
            }  
        }
    }
}