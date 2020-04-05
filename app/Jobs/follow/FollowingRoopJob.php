<?php
//1.TwitterAPIからターゲットのフォロワーを取得
//2.ターゲットのフォロワー保存準備&キーワード絞り込み
//3.ターゲットのフォロワー保存
//4.ループの判定・開始

namespace App\Jobs\follow;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use App\Domain\Services\ApiService\Tw\AdminApiRequest;
use App\Domain\Services\InsertSnsAccount;
use Log;
use App\Jobs\follow\TargetFollowerRoopJob;
use App\Domain\Services\ApiService\Tw\CheckApiRequest;
use App\Tw_Target_Friend;
use App\Jobs\unfollow\TargetFriendRoopJob;
            

class FollowingRoopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, 
    AdminApiRequest, InsertSnsAccount, CheckApiRequest;

    public $back_dto;//FollowerRoop::dispacthに戻すdto
    public $dto;
    public $counter; //フォロー対象を取り出す
    public $account_counter; //ターゲットアカウントを取り出す

    public $options;
    public $queue_name = "friendships_create";
    public $queue_name2 = "followers_list";
    public $auto_function_name = "follow";
    /**
     * Create a new job instance.
     *
     * @return void
     */

     public function __construct(
        $back_dto, 
        $account_counter, //ターゲットアカウント
        $counter = 0, 
        $options = ['restart_flag'=>false, "target_follower_list"=>array() ])
    {
        //
        Log::debug("フォロー開始");
        $this->back_dto = $back_dto;
        $this->options = $options;

        $request_url = 'https://api.twitter.com/1.1/friendships/create.json';

        $this->dto = [
            "api_requst" => "friendships_create",
            "my_tw_account"=>$back_dto['my_tw_account'],
            "request_method"=> 'POST',
            "request_url"=>$request_url, 
            "params"=> ['user_id'],
            'queue_name' => $back_dto['queue_name2'],//friendships_create
            'queue_id' => "",
        ];


        //$this->back_dto['target_follower_list']からcounterで取り出し、ここにフォロー対象のuser_idを格納する
        $this->dto["params"]['user_id'] = "";
        //フォロー対象をtarget_follower_listから取ってくるindex
        $this->counter = $counter;

        //FollowerRoop発行で使う
        $this->account_counter = $account_counter;
        

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            Log::debug('Job：FollowingRoopJob');

            //削除依頼対応
            $my_account = $this->getRealTime_Accunt();
            if ($my_account->getAutoFunc($this->auto_function_name) == 5) {
                $this->initApi_Request();
                return;
            }

            //再開Jobの時
             if ($this->options['restart_flag'] == true) {
                Log::debug('Job再開：FollowingRoopJob');
                $my_account = $this->getRealTime_Accunt();
                $my_account->setApi_status($this->queue_name, 1);//再開中から起動中に復帰
                $my_account->save();
            }

            //復帰jobで使う
            $queu_id = $this->job->getJobId();
            $this->dto['queue_id'] =  $queu_id;//faile_jobテーブルのインサートで使用
            $my_account = $this->getRealTime_Accunt();
            $my_account->setQueue_Id($this->auto_function_name, $queu_id);//再開する時、job_idを指すため
            $my_account->save();
            $this->dto['my_tw_account'] = $my_account;
            
            Log::debug("イベント発行");
            Log::debug($this->counter);
            $target_follower = $this->options['target_follower_list'][$this->counter];

            //フォロー対象を指定
            $this->dto['params']["user_id"] = $target_follower->target_friend_user_id;

            //リクエスト送信
            $result = $this->requestApi();

        //APIリクエストの上限と状態を確認
            if ($this->checkApiRestrictionAndStatus(
                $result, $this->back_dto, 
                $this->queue_name
                ) == true) 
            {
                $this->delete();
                return;
            };

           Log::debug('checkapi ok');
   
            //ブロックされていない場合
            //すでにフォロー済みでない場合
            if (!isset($result->errors)) {
                
                $target_follower->follow_at = date("Y/m/d H:i:s");
                $target_follower->save();

                $tw_account_id = $this->dto['my_tw_account']->getKey();
                $key_pattern_id = $this->dto['my_tw_account']->key_pattern_id;

                $count = Tw_Target_Friend::where([
                    'tw_account_id' => $tw_account_id,
                    'key_pattern_id' => $key_pattern_id,
                ])->where('follow_at', '!=', null)->count();
                
                Log::debug('フォロー数　：　'.$count);
                if ($count >= 5000) {

                    $my_account = $this->getRealTime_Accunt();
                    $my_account->unfollow_flag = true;
                    $my_account->save();

                    if ($my_account->getAutoFunc('unfollow') == 0) {
                        $this->dispatchUnfollowJob($my_account);
                    }

                }
    
            } else {
                //ブロックされているターゲットフォロワーは削除
                $target_follower->blocked = true;
                $target_follower->delete();
                $target_follower->save();
            }
    
            //次のフォローに移る
            $this->counter ++;


            //フォロー処理が完了したら、次のターゲットアカウントの処理に映る
            if ($this->back_dto['params']['cursor'] == 0 && $this->counter >= count($this->options['target_follower_list']) ) {
                $this->goNextTargetAccount();
                return;
            }

            //現在選択中のターゲットのフォローワーをさらに取得
            if ($this->counter >= count($this->options['target_follower_list'])) {
                $this->goNextCursor();
                return;
            }

            //次のフォロー処理に移る
            Log::debug('delay');
            Log::debug(now()->addMinutes(config('api.DEFAULT_QUEUE2')));
            self::dispatch($this->back_dto, $this->account_counter, $this->counter, $this->options)
            ->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE2')));
            
            $this->delete(); 
      
            return;
            
        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            $this->initApi_Request();
        }
    }

    

    public function requestApi () {

      //1.TwitterAPIからターゲットのフォロワーを取得
        Log::debug("リクエスト送信：自動フォロー");
        Log::debug("フォロー対象".$this->dto['params']["user_id"]);
        $obj = $this->accessTwitterAPI(
            $this->dto['api_requst'],
            $this->dto['my_tw_account'], 
            $this->dto['request_method'], 
            $this->dto['request_url'], 
            $this->dto['params']
        );
        
        return $obj;
    }

    public function goNextCursor () {

        Log::debug('go to next cursor');
        $this->setFinishAndStartForApiStatus($this->queue_name, $this->queue_name2);

        TargetFollowerRoopJob::dispatch($this->back_dto, $this->account_counter)
        ->onQueue($this->auto_function_name)
        ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
        $this->delete();

        return;

        
    }
    public function goNextTargetAccount () {

        //次のターゲット
        $this->account_counter ++;
        if ($this->account_counter >= (count(($this->back_dto['tw_target_account_list'])->toArray())) ) {
            $this->finishAutoFunc($this->auto_function_name, $this->dto['my_tw_account']);
            Log::debug("自動フォロー完了　job消化");
            return;
        }

        //account_counter 更新
        //next_cursor : -1に変更
        //user_idを更新
        Log::debug("次のターゲットからフォロワーターゲットリスト作成開始");
        $this->back_dto['params']['cursor'] = -1;
        $this->back_dto['params']['user_id'] = $this->back_dto['tw_target_account_list'][$this->account_counter]->target_account_user_id;
        
        $this->setFinishAndStartForApiStatus($this->queue_name, $this->queue_name2);

        TargetFollowerRoopJob::dispatch($this->back_dto, $this->account_counter)
        ->onQueue($this->auto_function_name)
        ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
        $this->delete();

        return;

        
    }

    //5000件を越えるとアンフォロー機能が起動します。
    public function dispatchUnfollowJob($my_account)
    {                        
        $my_account->unfollow = 1;
        $my_account->save();

        $request_method = 'GET' ;
        $params = array(
          "user_id" => "",
        );
  
        $job_dto = [
            "target_list" => "",//job内で改めて取得するため空です
            "request_method"=>$request_method, 
            "params"=>$params, 
            "my_tw_account"=>$my_account,
            'next' => null, 
    
            //ペジネーションのitemsでselectするコラム名
            'select_columns' => [
                                  'target_friend_id',
                                  'target_friend_user_id',
                                ],
    
            //リクエストとQueue管理用
            'api_requst' => 'users_show',
            "api_requst2" => 'friendships_lookup',
            'api_requst3' => 'friendships_destroy',
            "queue_name" => "users_show",
            "queue_name2" => "friendships_lookup",
            "queue_name3" => "friendships_destroy",
            'queue_id' => 0,
            'queue_id2' => 0,
            'queue_id3' => 0,
          ];
          Log::debug('アンフォロー機能初回発行');
          TargetFriendRoopJob::dispatch($job_dto)
          ->onQueue('unfollow')
          ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
      
    }
}

