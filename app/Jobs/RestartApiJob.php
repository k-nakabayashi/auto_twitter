<?php
//APIリクエストに制限を解く

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Tw_Account;
use App\Facades\ErrorService;
use App\Tw_Auto_Tweet;
use Log;

class RestartApiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tw_account_id;
    public $failed_id;
    public $api_request;
    public $auto_function_name;
    public $tweet_id;
    /**
     * Create a new job instance.

     * @return void
     */
    public function __construct(
        $tw_account_id, 
        $failed_id, 
        $api_request, 
        $auto_function_name,
        $tweet_id = null
        )
    {
        $this->tw_account_id = $tw_account_id;
        $this->failed_id = $failed_id;
        $this->api_request = $api_request;
        $this->auto_function_name = $auto_function_name;
        $this->tweet_id = $tweet_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            Log::debug('再開Job　開始 : account_id : '.$this->tw_account_id);

            Log::debug($this->tw_account_id);
            Log::debug($this->failed_id);
       
            
            //状態更新
            $account = Tw_Account::find($this->tw_account_id);        
            $account->setAutoFunc($this->auto_function_name ,1);

            if ($this->auto_function_name == 'tweet') {

                $model = Tw_Auto_Tweet::find($this->tweet_id);
                $model->tweet_status = 1;
                $model->save();
                
            } else {
                $account->setApi_Reqeust($this->api_request ,1);
            }

            $account->save();
            
            $artisan = base_path(). DIRECTORY_SEPARATOR. "artisan";
            $cmd = $artisan." queue:retry $this->failed_id";
            Log::debug("cmd : $cmd");
            Log::debug(json_encode(exec($cmd)));
           

        } catch (\Exception $e) {
            Log::debug($e);
            Log::debug("エラー発生：予期せぬエラーが起きました。");
            ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');

        }

    }
}


// $query = DB::table('tw__acounts')->where('tw_account_id', 1)->first();
