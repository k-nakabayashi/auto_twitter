<?php
namespace App\Domain\Services\FollowService;
use App\Facades\QueuePid;
use App\Jobs\follow\TargetFollowerRoopJob;
use App\Key_Pattern;
use Log;
use Illuminate\Support\Facades\Auth;
use App\Tw_Target_Account;
use App\Tw_Account;
use App\Facades\ErrorService;
use App\Domain\Services\AutoFunctionInterface;

class FollowStart implements AutoFunctionInterface {

  public $request;

  public function __construct()
  {
    $this->request = app('Illuminate\Http\Request');
    
  }
  
  public function __invoke() {
  
    Log::debug('service FollowStart');

  try {
    
    //1.Requestから自分のTwitterアカウントIDとターゲットアカウントIDを取得
    $req = $this->request;
    if (count($req->all()) <= 0) {
      var_dump("エラー発生：必要なパラメータが存在しません");
      ErrorService::setMessage('・エラー発生：必要なパラメータが存在しません');
      return;
    }


    //自分のtwitterアカウント：キーワードとアクセストークン取得で使う
    $id = $req->input('tw_account_id');
    $my_tw_account = Tw_Account::find($id);
    
    if ( $my_tw_account->suspended == true) {
      ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
      return true;
    }

    //api起動判定
    if ($my_tw_account->follow !=0) {
      ErrorService::setMessage('・このアカウントは 「自動いいね」は起動中。');
      ErrorService::setMessage(' 新たに「自動いいね」を起動する場合、一度、機能の停止を行なってください。');
      ErrorService::setMessage(' なお、停止すると、Twtterの仕様上、「自動いいね」の再起動に少々をお時間をいただきます。');
      return;
    } 

    //2.ターゲットアカウント：フォロワー取得で使う
    $tw_target_account_list = Tw_Target_Account::where('app_id', Auth::id())->where('deleted_at', null)
    ->orderBy('created_at')->get();
    
    if (count($tw_target_account_list)==0) {
      ErrorService::setMessage('・ターゲットアカウント登録数が０です。ターゲットアカウントを登録してください。');
      return;
    }


    //APIリクエストの設定
    $request_method = 'GET' ;
    $params = array(
      "user_id" => $tw_target_account_list[0]->target_account_user_id,//一人目分
      "include_entities" => false,
      "count" => config("api.TW_GET_COUNT"),
      "skip_status" => false,
      'cursor' => '-1',
    );
    $request_url = 'https://api.twitter.com/1.1/followers/list.json';
        
      //キーワード検索の準備
      $keyword = (json_decode(Key_Pattern::find($my_tw_account->key_pattern_id)->toArray()['keyword']));
      for ($i = 0; $i < count($keyword); $i ++) {
        $keyword[$i] = json_decode($keyword[$i]);
      }



      //4.Job発行 キーワード絞り込み行い、ターゲットフォロワーアカウントを作成する         
      //Request上限判定
      $checkapi = Tw_Account::find($id)->getApi_Reqeust("followers_list");//リアルタイムで判定するため、再度モデルを作っています

      if ($checkapi == false) {
        ErrorService::setMessage('・フォロー機能は重複起動は行えません。制限中です。');
        return;

      } else {

        //Wokerの起動
        $queue_name = "follow";
        $my_tw_account->follow = 1;
        $my_tw_account->save();

        $job_dto = [
          "tw_target_account_list"=>$tw_target_account_list,
          "request_method"=>$request_method, 
          "request_url"=>$request_url, 
          "params"=>$params, 
          "my_tw_account"=>$my_tw_account,
          "keyword" => $keyword,

          //リクエストとQueue管理用
          "api_requst" => 'followers_list',    
          "queue_name" => "followers_list",
          "queue_name2" => "friendships_create",
          'queue_id' => 0,
        ];

        Log::debug("遅延：".config('api.DEFAULT_QUEUE'));
        TargetFollowerRoopJob::dispatch($job_dto, 0)
        ->onQueue($queue_name)
        ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));

        return $my_tw_account;
      }
  
    } catch (\Exception $e) {
  
      var_dump($e);
      Log::debug($e);
      var_dump("エラー発生：予期せぬエラーが起きました。");
      ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
      return;
    }

    
    //ターゲットアカウントをreturn;
    return;
  }

  public function stop(){
    return null;
  }
  public function restart(){
    return null;
  }
}
