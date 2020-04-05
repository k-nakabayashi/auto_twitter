<?php
//input
// tweet_timing:
// tw_tweet_id: 
// tw_account_id:
// detail:
// tags: 
// model_name:

namespace App\Domain\Services\TweetService;
use App\Jobs\tweet\ReservingTweetJob;
use Log;
use App\Tw_Account;
use App\Facades\ErrorService;
use Carbon\Carbon;
use App\Tw_Auto_Tweet;
use App\Domain\Services\AutoFunctionInterface;


class TweetStart implements AutoFunctionInterface {

  public $req;
  public $repogitory;

  public function __construct()
  {
    $this->req = app('Illuminate\Http\Request');
    $this->repogitory = app('App\Domain\Repogitories\RESTfulDAO\CommonDAOInterface');
  }
  
  public function __invoke() {
    Log::debug('service : TweetStart');
	
    try {

      //1.Requestから自分のTwitterアカウントID　　　　　　　　　　　
      $id = $this->req->input('id');
      $auto_tweet = Tw_Auto_Tweet::find($id);
      $my_tw_account = Tw_Account::find($auto_tweet->tw_account_id);

      if ( $my_tw_account->suspended == true) {
        ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
        return true;
      }
 
      //リクエスト制限判定
      if ($my_tw_account->statuses_update == 0) {
        ErrorService::setMessage('・制限中です。復旧まで少々お待ちください。');
        return;
      }

      //2.投稿予約
      $now = new Carbon(date('Y/m/d H:i'));
      $tweet_timing = new Carbon($this->req->input('tweet_timing'));
      if ($now->gt($tweet_timing)) {
        ErrorService::setMessage('・投稿日時を正しく設定されていません。');
        return;
      }

      $errors = ErrorService::getMessage();
      if (count($errors) > 0) {
        return;
      }      
      
      //3.投稿内容のフォーマット
      $fromat_tags = preg_replace('/\n|\[|\]|\"|( |　)+/', "", $auto_tweet->tags);
      $array_tags = preg_split("/,/", $fromat_tags);
      $tags = "";
      for ($i = 0; $i < count($array_tags); $i ++) {
        $tags .= "#".$array_tags[0];

        if (($i + 1) < count($array_tags)) {
          $tags .= " ";
        }
      }

      //4.APIリクエストの設定
      $request_method = 'POST' ;
      $params = array(
        "status" => $auto_tweet->detail."\n".$tags,
      );
      $request_url = 'https://api.twitter.com/1.1/statuses/update.json';


      //5.Jobの設定
      $job_dto = [
        "tweet_id" => $auto_tweet->getKey(),
        "request_method"=>$request_method, 
        "request_url"=>$request_url, 
        "params"=>$params, 
        "my_tw_account"=>$my_tw_account,
        'api_requst' => 'statuses_update',
        'queue_id' => 0,
      ];


      //4.予約日時設定
      $now = new Carbon(date('Y/m/d H:i'));
      $delay = $tweet_timing->diffInMinutes($now);
      Log::debug('now : '.$now );
      Log::debug('delay : '.$tweet_timing );
      
      //Wokerの設定
      $my_tw_account->follow = 1;
      $my_tw_account->save();

      $auto_tweet->tweet_status = 1;//予約中に状態を更新
      $auto_tweet->tweet_timing = $tweet_timing;
      $auto_tweet->save();
      
      //5.Job発行
      Log::debug("遅延：$delay 分");
      $test = ReservingTweetJob::dispatch($job_dto)
      ->delay(now()->addMinutes($delay));
      Log::debug(json_encode($test));
      return $my_tw_account;
        
    } catch (\Exception $e) {

      var_dump($e);
      Log::debug($e);
      var_dump("エラー発生：予期せぬエラーが起きました。");
      ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
    }

    
    //ターゲットアカウントをreturn;
    return;
  }
}
