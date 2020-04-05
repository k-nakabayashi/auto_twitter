<?php
//APIリクエストに制限を解く

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Tw_Account;
use App\ApiCounter;
use Log;
class ReleaseApiRestrictionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $model;
    public $api_request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Tw_Account $model, $api_request)
    {
        $this->model = $model;
        $this->api_request = $api_request;        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //API制限を解除する
        $id = $this->model->getKey();
        $model = Tw_Account::find($id);
        Log::debug("制限解除 : ".$this->api_request);
        $model->setApi_Reqeust($this->api_request, 1);//制限解除
        $model->save();

        //api_counterの初期化
        $api_couter_id =  $model->getApi_Counter($this->api_request);
        $api_couter = ApiCounter::find($api_couter_id);
        $api_couter->counter = 0;
        $api_couter->save();
    }
}
