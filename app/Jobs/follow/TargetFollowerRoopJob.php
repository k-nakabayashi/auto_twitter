<?php
//self:dispatch,jobループdispatchを使用して、
//ターゲットのフォロワー取得・フォロー処理を行なっています。
//各種処理の前に、api_request上限に達しているか？判定あり。
//tw_accoutテーブルのカラムで判定しています。

//1.TwitterAPIからターゲットのフォロワーを取得
//2.フォロワーが0の時は、次のターゲットにうつる
//3.フォロワーの絞り込み＆保存
//4.追加したフォロワーのuser_idを取得
//5.残りのフォロワーを取得
//6.自動フォロー開始

// checkApiRestrictionAndStatus()　： リクエスト上限・起動状態を確認し、問題があればself::dispatch()


namespace App\Jobs\follow;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use App\Domain\Services\ApiService\Tw\AdminApiRequest;
use App\Domain\Services\InsertSnsAccount;
use App\Tw_Target_Friend;   
use App\Jobs\follow\FollowingRoopJob;

use Log;
use App\Facades\ErrorService;
use App\Domain\Services\ApiService\Tw\CheckApiRequest;
            

class TargetFollowerRoopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, 
    AdminApiRequest, InsertSnsAccount, CheckApiRequest;

    public $dto;
    public $account_counter = 0;
    public $options;
    public $queue_name = "followers_list";
    public $queue_name2 = "friendships_create";
    public $auto_function_name = "follow";
    /**
     * Create a new job instance.
     *
     * @return void
     */
    // public function __construct()
    public function __construct(
        $dto,
        $account_counter = 0, 
        $options = ['restart_flag'=>false, "target_follower_list"=>array() ])
    {

        $this->dto = $dto;
        $this->options = $options;
        $this->account_counter = $account_counter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    
    public function handle()
    {

        
        Log::debug("roop");
        Log::debug("TargetFollowerRoopJob");
        Log::debug("対象のカーソル：".$this->dto['params']['cursor']);

        try {
            
            //削除依頼対応
            $my_account = $this->getRealTime_Accunt();
            if ($my_account->getAutoFunc($this->auto_function_name) == 5) {
                $this->initApi_Request();
                return;
            }

             //init: 初期準備
            $queue_id = $this->job->getJobId();
            $this->dto['queue_id'] =  $queue_id;//faile_jobテーブルのインサートで使用
            Log::debug("queu_id : ".$queue_id);
            $my_account = $this->getRealTime_Accunt();
            $my_account->setApi_Status($this->queue_name, 1);//「起動中」という判定になる
            $my_account->setQueue_Id($this->auto_function_name, $queue_id);//再開する時、job_idを指すため
            $my_account->save();
            $this->dto['my_tw_account'] = $my_account;
            
            Log::debug('ターゲットリストのカウンター : '.$this->account_counter);
            Log::debug('ターゲットアカウント名前：'.$this->dto['tw_target_account_list'][$this->account_counter]->name);

            //1.TwitterAPIからターゲットのフォロワーを取得
            $tw_account_FromTw = $this->getFollowers();
           
            //APIリクエストの上限と状態を確認
            if ($this->checkApiRestrictionAndStatus(
                    $tw_account_FromTw, $this->dto, 
                    $this->queue_name)
                == true) 
            {
                $this->delete();
                return;
            };

            //2.フォロワーが0の時は、次のターゲットにうつる
        
            if (empty($tw_account_FromTw->users)) {
                
                // 次のターゲットがいるか確認
                if ($this->account_counter < (count(($this->dto['tw_target_account_list'])->toArray())-1) ) {
                
                    $this->goNextTargetAccount();
                    $this->delete();
                    return;

                } else {
                    $this->finishAutoFunc($this->auto_function_name, $this->dto['my_tw_account']);
                    Log::debug("フォロワーも次のターゲットも存在しません");
                    return;
                }
            }

            //3.フォロワーの絞り込み＆保存
            $id_list_inserted = $this->saveFollowers($tw_account_FromTw);
            
            //4.追加したレコードのuser_idを取得000
            Log::debug('next curosr');
            Log::debug("id_list_inserted : ".count($id_list_inserted));
            if (count($id_list_inserted) == 0 ) {
                if($this->getRestOfFollowers($tw_account_FromTw->next_cursor ) == true) {
                    Log::debug('getRestOfFollowers');
                    $this->delete();
                    return;
                };
                $this->goNextTargetAccount();
                $this->delete();
                return;
            }
            foreach ($id_list_inserted as $id) {
                array_push($this->options['target_follower_list'], Tw_Target_Friend::find($id));
            }
            
            //次のフォロワー取得の準備
            $this->dto['params']['cursor'] = $tw_account_FromTw->next_cursor;

            //5.残りのフォロワーを取得※残りがあればself::dispatchでループする
            // Log::debug("next : ".$tw_account_FromTw->next_cursor);
            // if($this->getRestOfFollowers($tw_account_FromTw->next_cursor ) == true) {
            //     $this->delete();
            //     return;
            // };

            //
            

            //6.自動フォロー開始
            // if ($tw_account_FromTw->next_cursor == 0) {

                Log::debug("フォロワーターゲットリスト作成完了");
              
                // //抽出したフォロワーが0の時は、次のターゲットに移る
                // if (count($this->options['target_follower_list']) == 0) {
                //     Log::debug('roop  次のターゲットアカウントに映ります');
                //     $this->goNextTargetAccount();
                //     return;
                // }

                //状態切り替え
                $this->setFinishAndStartForApiStatus($this->queue_name, $this->queue_name2);

                //フォロー処理jobを時間差遅延dispatch
                //ここを処理分割する
                //FollowingRoopJobのリクエストエラーは、FollowingRoopJob内で補足しますので、ここではdispatchします
                FollowingRoopJob::dispatch($this->dto, $this->account_counter, 0, $this->options)
                ->onQueue($this->auto_function_name)
                ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE2')));
                $this->delete();
                return;
            // }
        

        } catch (\Exception $e) {
         
            Log::debug($e);
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            $this->initApi_Request();
        }
    }

    public function getFollowers () {
  
        //"user_id"に注意
        $tw_account_FromTw = $this->accessTwitterAPI(
            $this->queue_name,
            $this->dto['tw_target_account_list'][$this->account_counter], 
            $this->dto['request_method'], 
            $this->dto['request_url'], 
            $this->dto['params'], 
            $this->dto['my_tw_account']
        ); 

        // $json = file_get_contents("tw_account_friend.json");
        // $jsonString = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        // $arr = json_decode($jsonString,true);
        // $json = json_encode($arr,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // $tw_account_FromTw = json_decode( $json );
            
      
        return $tw_account_FromTw;
    }

    public function saveFollowers($tw_account_FromTw) {

            //ターゲットのフォロワー保存準備&キーワード絞り込み&非公開アカウント除外
            Log::debug("使用キーワード");
            Log::debug($this->dto['keyword']);
            $tw_target_friend_list = InsertSnsAccount::prepareInsert(
                $this->dto['tw_target_account_list'][$this->account_counter], 
                $this->dto['my_tw_account'], 
                $tw_account_FromTw,
                $this->dto['keyword']
            );
            // Log::debug($tw_target_friend_list);
            
            //ターゲットのフォロワー保存
            $tw_target_friend_model = app('App\Tw_Target_Friend');
            
            $id_list_inserted = InsertSnsAccount::insertFriendRecord(
                $this->dto['tw_target_account_list'][$this->account_counter], 
                $tw_target_friend_list, 
                $tw_target_friend_model,
                $this->dto['my_tw_account']
            );

            return  $id_list_inserted;
    }


    //次の100フォローワーの有無を返します。
    public function getRestOfFollowers ($next_cursor) {

        if ($next_cursor != 0) {
            Log::debug($next_cursor);
            $this->dto['params']['cursor'] = $next_cursor;
            Log::debug("next job  following");

            self::dispatch($this->dto, $this->account_counter, $this->options)
            ->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
            return true;
        } 
    }

    public function goNextTargetAccount () {


        //次のターゲットのフォロワー抽出開始
        if ($this->account_counter < (count(($this->dto['tw_target_account_list'])->toArray())-1) ) {
            Log::debug("次のターゲっとを取得");
            //account_counter 更新
            //next_cursor : -1に変更
            //user_idを更新
            Log::debug("次のターゲットからフォロワーターゲットリスト作成開始");
            $this->account_counter ++;
            $this->dto['params']['cursor'] = -1;
            $this->dto['params']['user_id'] = $this->dto['tw_target_account_list'][$this->account_counter]->target_account_user_id;

            self::dispatch($this->dto, $this->account_counter)
            ->onQueue($this->auto_function_name)
            ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
            $this->delete();
            return true;

        } else {
            $this->finishAutoFunc($this->auto_function_name, $this->dto['my_tw_account']);
            Log::debug("次のターゲットはないです");
            Log::debug("完全にフォロワーターゲットリスト作成完了");
        }
    }

}

