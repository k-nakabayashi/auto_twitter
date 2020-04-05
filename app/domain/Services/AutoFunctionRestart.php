<?php
namespace App\Domain\Services;
use Illuminate\Support\Facades\Request;
use App\Tw_Account;
use Illuminate\Support\Facades\DB;
use App\Jobs\RestartApiJob;
use App\Facades\ErrorService;
use Log;
use App\Domain\Services\SuspendChecker;
/**
 * TODO Auto-generated comment.
 */
class AutoFunctionRestart {
	use SuspendChecker;

	public $auto_function_name;

	public function __construct()
	{
		$request = app("Illuminate\Http\Request");
		
		
		if (!empty($request->input("domain"))) {
			$this->auto_function_name = $request->input("domain");
		} else if (!empty($request->input("domain2"))) {
			$this->auto_function_name = $request->input("domain2");
		} else if (!empty($request->input("domain3"))) {
			$this->auto_function_name = $request->input("domain3");
		}
		Log::debug($request->input("domain2"));

	}

	public function __invoke() {
		try {
			Log::debug('restart service : FavoriteRestart');

			$tw_account_id = Request::input('tw_account_id');

			//凍結判定
			if (Tw_Account::find($tw_account_id)->suspend == true) {
				//普通に起動する際に、凍結フラグを判定
				ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
				return;
			}
			
			$tw_account = Tw_Account::find($tw_account_id);

		//再開可能か確認
			if ( $tw_account->getAutoFunc($this->auto_function_name) != 3) {
				Log::debug("予期せぬエラー：再開できる状態ではありません");
				ErrorService::setMessage('予期せぬエラー：再開できる状態ではありません');
				return;
			}
	

			//対象のfailed_jobを抽出
			$api_request1 = Request::input('queue_name1');
			$api_request2 = Request::input('queue_name2');
			$api_request3 = Request::input('queue_name3')? Request::input('queue_name3') : null;

			//queu retryの外部コマンド実行の際に使用するfailed_idを取得
			$queue_id = $tw_account->getQueue_Id($this->auto_function_name);
			$failed_queue = DB::table('failed_jobs')->where('queue_id', $queue_id)->first();


			//再開Job発行
			//failed_queueの有無を確認
			// return;
			if ($failed_queue) {

				Log::debug('再開Job1　発行');


				$api_request = "";

				if ($tw_account->getApi_Status($api_request1) == 3) {

					$tw_account->setApi_Status($api_request1, 2);
					$api_request = $api_request1;

				} else if ($tw_account->getApi_Status($api_request2) == 3) {

					$tw_account->setApi_Status($api_request2, 2);
					$api_request = $api_request2;

				} else if ($tw_account->getApi_Status($api_request3) == 3) {
					
					$tw_account->setApi_Status($api_request3, 2);
					$api_request = $api_request3;
				}

				RestartApiJob::dispatch($tw_account_id, $failed_queue->id, $api_request, $this->auto_function_name)
				->delay(now()->addMinutes(config('api.RESTART_QUEUE')));

				//状態の更新 : 「再開中」になる
				$tw_account->setAutoFunc($this->auto_function_name, 2);
				$tw_account->save();
				
				return $tw_account;

			} else {
				Log::debug("まだ「再開」可能な状態ではありません。少々お待ちください。");
				ErrorService::setMessage('まだ「再開」可能な状態ではありません。少々お待ちください。');
				return;
			}


				
			} catch (\Exception $e) {
				Log::debug($e);
				var_dump("エラー発生：予期せぬエラーが起きました。");
				ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
				return;
			}
	}

}
