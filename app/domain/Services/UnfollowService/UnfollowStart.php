<?php
namespace App\Domain\Services\UnfollowService;
use App\Jobs\unfollow\TargetFriendRoopJob;
use Log;
use App\Tw_Account;
use App\Facades\ErrorService;
use App\Domain\Services\AutoFunctionInterface;
use App\Tw_Target_Friend;

class UnFollowStart implements AutoFunctionInterface{

  public $req;

  public function __construct()
  {
    $this->req = app('Illuminate\Http\Request');
  }
  
  public function __invoke() {

    try {
    
      //1.Requestから自分のTwitterアカウントID
      Log::debug('unfollow start');
      $id = $this->req->input('tw_account_id');
      $my_tw_account = Tw_Account::find($id);

      if ( $my_tw_account->suspended == true) {
        ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
        return true;
      }

      //api起動判定
      if ($my_tw_account->unfollow !=0) {
        ErrorService::setMessage('・このアカウントは 「自動アンフォロー」は起動中。');
        ErrorService::setMessage(' 新たに「自動アンフォロー」を起動する場合、一度、機能の停止を行なってください。');
        ErrorService::setMessage(' なお、停止すると、Twtterの仕様上、「自動アンフォロー」の再起動に少々をお時間をいただきます。');
        return;
      } 

      //2.フォロー済みリストの有無を確認
      $tw_target_friesnd_list = Tw_Target_Friend::where("tw_account_id", $my_tw_account->getKey())
      ->get()->toArray();
      if (count($tw_target_friesnd_list) <= 0) {
        ErrorService::setMessage('・フォロー済みのリストが空です。自動フォロー機能を使い、フォローを増やしてください。');
        return;
      }

      //APIリクエストの設定
      $request_method = 'GET' ;
      $params = array(
        "user_id" => "",
      );

      //3.Jobの設定
      $my_tw_account->setApi_Status('users_show',1);
      $my_tw_account->unfollow = 1;
      $my_tw_account->save();

      $job_dto = [
        "target_list" => "",//job内で取得するため空です
        "request_method"=>$request_method, 
        "params"=>$params, 
        "my_tw_account"=>$my_tw_account,
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

      //4.Job発行
      //Request上限判定  //入り口である,users_showで判定しています
      $checkapi = Tw_Account::find($id)->getApi_Reqeust($job_dto['api_requst']);//リアルタイムで判定するため、再度モデルを作っています

      if ($checkapi == false) {
        TargetFriendRoopJob::dispatch($job_dto, 0)
        ->onQueue('unfollow')
        ->delay(now()->addMinutes(config('api.RESTART_QUEUE')));
        
        return $my_tw_account;
        
      } else {
        
        Log::debug("遅延：".config('api.DEFAULT_QUEUE'));

        TargetFriendRoopJob::dispatch($job_dto)
        ->onQueue('unfollow')
        ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
        return $my_tw_account;

      }
        
      return;
  
    } catch (\Exception $e) {
  
      var_dump($e);
      Log::debug($e);
      var_dump("エラー発生：予期せぬエラーが起きました。");
      ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
      return;
    }

    

  }
}
