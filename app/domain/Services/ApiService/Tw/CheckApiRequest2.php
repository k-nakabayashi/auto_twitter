<?php
//自動ツイートの判定
//CheckApiRequestと同じことをしていますが、tweetはアカウントに対して1対多のためにわけております。
namespace App\Domain\Services\ApiService\Tw;

use App\Tw_Account;
use Log;

trait CheckApiRequest2 {


      //apiリクエストの制限判定
    //制限を食らうと、$tw_account_FromTwの中身は$response_codeになる
    public function checkApiRestrictionAndStatus2(
        $result, //twitterからのレスポンス結果
        $tweet,
        $dto,
        $api_request,
        $options = ['restart_flag'=>true]//再起動時のオプション:
        )
    {

        Log::debug("check result : ".$api_request);
        $tweet = $this->getRealTime_Tweet();

        //ピンポイントで補足：
        if (isset($result->errors)) {
            Log::debug('エラーリクエス補足');

            //凍結した場合
            if ($result->errors[0]->code == 63 || $result->errors[0]->code == 326) {
                Log::debug('凍結中'); 

                
                //一時停止
                $tweet->tweet_status = 3;
                $tweet->save();

                $this->fail();
                
                return true;
            }
            
            //twitterによる制限
            if ($result->errors[0]->code == 161) {
                Log::debug('24時間制限中');

                //再開中
                $tweet->tweet_status = 2;
                $tweet->save();

                self::dispatch($dto, $options)
                ->onQueue('tweet')
                ->delay(now()->addMinutes(config('api.RESTART_QUEUE2')));
               
                return true;

            } else {
                //再開中
                $tweet = $this->getRealTime_Tweet();
                $tweet->tweet_status = 2;
                $tweet->save();

                Log::debug(config('api.RESTART_QUEUE')."分後に再開します。");
                self::dispatch($dto, $options)
                ->onQueue('tweet')
                ->delay(now()->addMinutes(config('api.RESTART_QUEUE')));
                
                return true;
            }

        }

        $checkapi = Tw_Account::find($dto['my_tw_account']->getKey())->getApi_Reqeust($api_request);;
        if ($checkapi == true &&  $tweet->tweet_status == 3) 
        {              
            Log::debug('一時停止中');   
            $this->job->fail();
            return true;
        }
    }
}

?>