<?php

namespace App\Domain\Services\FavoriteService;

use App\Jobs\favorite\SearchTweetRoopJob;
use Log;
use App\Tw_Account;
use App\Favorite_Key_Pattern;
use App\Facades\ErrorService;
use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\SuspendChecker;
use App\Domain\Services\QueuePidAdmin;

class FavoriteStart implements AutoFunctionInterface{

  public $req;

  public function __construct()
  {
    $this->req = app('Illuminate\Http\Request');

  }
  
  public function __invoke() {
    Log::debug('FavoriteStart start');

    try {    
    
      //1.Requestから自分のTwitterアカウントID
      $id = $this->req->input('tw_account_id');
      $my_tw_account = Tw_Account::find($id);
      if ( $my_tw_account->suspended == true) {
        ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
        return true;
      }

      //api起動判定
      if ($my_tw_account->favorite != 0) {
        ErrorService::setMessage('・このアカウントは 「自動いいね」は起動中。');
        ErrorService::setMessage(' 新たに「自動いいね」を起動する場合、一度、機能の停止を行なってください。');
        ErrorService::setMessage(' なお、停止すると、Twtterの仕様上、「自動いいね」の再起動に少々をお時間をいただきます。');
        return;
      } 

      //2.クエリフォーマット
      $keyword = (json_decode(Favorite_Key_Pattern::find($my_tw_account->favorite_key_pattern_id)->toArray()['keyword']));
      for ($i = 0; $i < count($keyword); $i ++) {
        $keyword[$i] = json_decode($keyword[$i]);
      }
      $q = "";
      foreach ($keyword as $pare) {
        
        if ($pare->opt == "or") {
          $q .= 'OR '.$pare->txt;

        } else if ($pare->opt == "not") {
          $q .= '-'.$pare->txt;
        } else {
          //and
          $q .= $pare->txt;
        }
        $q .= ' '; 
      }

      Log::debug($q);

      //APIリクエストの設定
      $request_method = 'GET' ;
      $params = array(
        'q' => $q,
        'locale' => 'ja',
        'lang' => 'ja',
        'result_type' => 'mixed',
        'count' => config('api.TW_GET_COUNT'),
        'max_id' =>  "",
        'include_entities' => false,
      );

      //3.Jobの設定
      $my_tw_account->setApi_Status('search_tweets',1);
      $my_tw_account->favorite = 1;
      $my_tw_account->save();

      $job_dto = [
        'request_method'=>$request_method, 
        'params'=>$params, 
        'my_tw_account'=>$my_tw_account,

        //リクエストとQueue管理用
        'api_requst' => 'search_tweets',
        'api_requst2' => 'favorites_create',
        'queue_name' => 'search_tweets',
        'queue_name2' => 'favorites_create',
        'queue_id' => 0,
        'queue_id2' => 0,
        'request_method' => 'GET',
        'request_url' => 'https://api.twitter.com/1.1/search/tweets.json',
        'roop' => true,
      ];

  

      //4.Job発行
      //Request上限判定 
      $checkapi1 = Tw_Account::find($id)->getApi_Reqeust('search_tweets');
      $checkapi2 = Tw_Account::find($id)->getApi_Reqeust('favorites_create');

      if ($checkapi1 == false ||  $checkapi2 == false) {
        Log::debug("restart");
        SearchTweetRoopJob::dispatch($job_dto)
        ->onQueue('favorite')
        ->delay(now()->addMinutes(config('api.RESTART_QUEUE')));

      } else {
        
        Log::debug('遅延：'.config('api.DEFAULT_QUEUE'));

        SearchTweetRoopJob::dispatch($job_dto)
        ->onQueue('favorite')
        ->delay(now()->addMinutes(config('api.DEFAULT_QUEUE')));
      
      }
        
      return $my_tw_account;
  
    } catch (\Exception $e) {
  
      var_dump($e);
      Log::debug($e);
      var_dump('エラー発生：予期せぬエラーが起きました。');
      ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
    }
  }
}
