<?php
//accessTwitterAPI()での結果判定に対して、振る舞い
//Job再発行するか、停止するか。

namespace App\Domain\Services\ApiService\Tw;
use App\ApiCounter;
use Carbon\Carbon;
use App\Tw_Account;
use Log;

trait CheckApiRequest {


      //apiリクエストの制限判定
    //制限を食らうと、$tw_account_FromTwの中身は$response_codeになる
    public function checkApiRestrictionAndStatus(
        $result, //twitterからのレスポンス結果
        $dto,
        $api_request,
        $options = ['restart_flag'=>true]//再起動時のオプション: 状態が「２」→「１」となる
        )
    {
        $my_account = Tw_Account::find($dto['my_tw_account']->getkey());
        $checkapi = $my_account->getApi_Reqeust($api_request);
        $checkapi_status = $my_account->getApi_Status($api_request);
        Log::debug($api_request);
        Log::debug("制限状態：".$checkapi);
        Log::debug("使用状態：".$checkapi_status);


        // $job_id = $this->job->getJobId();
        if (isset($result->errors)) {
            //凍結した場合
            if ($result->errors[0]->code == 63 || $result->errors[0]->code == 326) {
                Log::debug('凍結中');

                $my_account->setApi_Status($api_request, 3);
                $my_account->save();

                $this->fail();
                return true;
            }
        }

        //API制限中
        if ($checkapi == false && $checkapi_status != 0) {
            Log::debug("制限");
            
            //ピンポイントで補足：
            if (isset($result->errors)) {

                //AdminApiRequestのaccessの時点で再開中「２」になっている

                //制限ではないが純粋なエラー: 次の処理に移る
                if ($result->errors[0]->code == 139 || $result->errors[0]->code == 144 ) {
                    //いいねのエラー：存在しないツイート、すでにいいね済み。
                    Log::debug($result->errors[0]->message);
                    return false;
                }

                //twitterによる制限
                if ($result->errors[0]->code == 161) {
                    Log::debug('24時間制限中');

                    if (isset($this->account_counter) && isset($this->counter)) {

                        self::dispatch($dto, $this->account_counter, $this->counter, $options)
                        ->onQueue($this->auto_function_name)
                        ->delay(now()->addMinutes(config('api.RESTART_QUEUE2')));

                    } else {

                        self::dispatch($dto, $options)
                        ->onQueue($this->auto_function_name)
                        ->delay(now()->addMinutes(config('api.RESTART_QUEUE2')));
                    }

                    return true;
                }

                //翌日０時に再開
                if ($result->errors[0]->code == config('api.TW_RESTRINCION_CODE')) {

                    $counter_id = $dto['my_tw_account']->getApi_Counter($api_request);
                    $api_acounter = ApiCounter::find($counter_id);
                    $api_acounter->counter = 0;
                    $api_acounter->save();
                    
                    $next_date = new Carbon($api_acounter->counting_started_at);//この処理の前に、counting_started_atは更新されています。
                    $now_date = new Carbon(date('Y/m/d H:i:s'));
                    $delay_for_next_start = $next_date->diffInMinutes($now_date);
                    
                    if (isset($this->account_counter)) {

                        self::dispatch($dto, $this->account_counter, $options)
                        ->onQueue($this->auto_function_name)
                        ->delay(now()->addMinutes($delay_for_next_start));

                    } else {

                        self::dispatch($dto, $options)
                        ->onQueue($this->auto_function_name)
                        ->delay(now()->addMinutes($delay_for_next_start));
                    }

                    return true;
                }

                if ($result->errors[0]->code == config('api.TW_RESTRINCION_CODE2')) {
                    //アプリ側の設定による制限
                    //ここの時点では制限済みです※すでに再開dispacth発行済
                    //ここに入るとリクエストは却下される
                    Log::debug('却下');
                    $this->initApi_Request();
                    return true;
                }
            }

            //15分後に再開
            if ($checkapi_status == 2) {
                //$this->checkApiで「再開中」になっているので、trueで「起動中」に変更
                Log::debug('同じJobを発行');
                $options['restart_flag'] = true;

                if (isset($this->account_counter)) {
                    Log::debug('here1');
                    self::dispatch($dto, $this->account_counter, $options)
                    ->onQueue($this->auto_function_name)
                    ->delay(now()->addMinutes(config('api.RESTART_QUEUE')));
                } else {
                    Log::debug('here1');
                    self::dispatch($dto, $options)
                    ->onQueue($this->auto_function_name)
                    ->delay(now()->addMinutes(config('api.RESTART_QUEUE')));
                }


                return true;
            }

        }
        
        //制限されていない時
        if ($checkapi == true &&  $checkapi_status == 3) 
        {              
            Log::debug('一時停止中');   
            $this->job->fail();
            return true;
        }
    }


}

?>