<?php
namespace App\Domain\Services;
use Illuminate\Support\Facades\Request;
use Log;
use App\Domain\Services\AutoFunctionInterface;
use App\Facades\ErrorService;
use App\Tw_Account;
/**
 * TODO Auto-generated comment.
 */
class AutoFunctionStop implements AutoFunctionInterface{

	public $auto_function_name;

	public function __construct()
	{


		$request = app("Illuminate\Http\Request");
		
		if (!empty($request->input("domain"))) {
			$this->auto_function_name = $request->input("domain");//follow
		} else if (!empty($request->input("domain2"))) {
			$this->auto_function_name = $request->input("domain2");//favorite
		} else if (!empty($request->input("domain3"))) {
			$this->auto_function_name = $request->input("domain3");//unfollow
		} else if (!empty($request->input("domain4"))) {
			$this->auto_function_name = $request->input("domain4");//tweet
		}
	}
	/**
	 * TODO Auto-generated comment.
	 * $model : tw_accout
	 */
	public function __invoke() {

		try {
			Log::debug('autofunction stop');

			$tw_account_id = Request::input('tw_account_id');
			$tw_account = Tw_Account::find($tw_account_id);
			if ($tw_account->suspended == true) {
				ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
				return true;
			}

			$api_request1 = Request::input('queue_name1');
			$api_request2 = Request::input('queue_name2');
			$api_request3 = Request::input('queue_name3')? Request::input('queue_name3') : null;

			if ($tw_account->getAutoFunc($this->auto_function_name) != 1) {
				Log::debug("予期せぬエラー：「起動中」ではありません。");
				ErrorService::setMessage('予期せぬエラー：「起動中」ではありません。');
				return;
			}


			//状態の更新 : 「一時停止中」3になる
			$api_request = "";

			if ($tw_account->getApi_Status($api_request1) == 1) {

				$tw_account->setApi_Status($api_request1, 3);
				$api_request = $api_request1;

			} else if ($tw_account->getApi_Status($api_request2) == 1) {

				$tw_account->setApi_Status($api_request2, 3);
				$api_request = $api_request2;

			} else if ($tw_account->getApi_Status($api_request3) == 1) {
				
				$tw_account->setApi_Status($api_request3, 3);
				$api_request = $api_request3;
			}

			if ($api_request == "") {
				Log::debug("まだ「一時停止」可能な状態ではありません。少々お待ちください。");
				ErrorService::setMessage('まだ「一時停止」可能な状態ではありません。少々お待ちください。');
				return;
			}

			$tw_account->setAutoFunc($this->auto_function_name, 3);
			$tw_account->save();
			return $tw_account;
			
		} catch (\Exception $e) {
			Log::debug($e);
			var_dump("エラー発生：予期せぬエラーが起きました。");
			ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
			return;
		}
	}

	public function stopTweet () {

	}
}
