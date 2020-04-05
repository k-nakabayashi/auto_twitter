<?php
//tweet以外の自動機能Jobの削除を行います。

namespace App\Domain\Services;
use Illuminate\Support\Facades\Request;
use Log;
use App\Facades\ErrorService;
use App\Tw_Account;
use Illuminate\Support\Facades\DB;


/**
 * TODO Auto-generated comment.
 */
class AutoFunctionDelete {

	public $auto_function_name;
	public $req;
	public function __construct()
	{

		$this->req = app("Illuminate\Http\Request");
		
		if (!empty($this->req->input("domain"))) {
			$this->auto_function_name = $this->req->input("domain");
		} else if (!empty($this->req->input("domain2"))) {
			$this->auto_function_name = $this->req->input("domain2");
		} else if (!empty($this->req->input("domain3"))) {
			$this->auto_function_name = $this->req->input("domain3");
		}


	}

	/**
	 * TODO Auto-generated comment.
	 * $model : tw_accout
	 */
	public function __invoke() {

		try {
			Log::debug($this->auto_function_name.'：delete');
			$tw_account_id = $this->req->input('tw_account_id');
			
			if (Tw_Account::find($tw_account_id)->suspend == true) {
				ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
				return true;
			}

			//制限中は削除不可
			$api_request1 = $this->req->input('queue_name1');
			$api_request2 = $this->req->input('queue_name2')? $this->req->input('queue_name3') : null;
			$api_request3 = $this->req->input('queue_name3')? $this->req->input('queue_name3') : null;

		
			$flag = false;

			$tw_account = Tw_Account::find($tw_account_id);
			if ($tw_account->getApi_Reqeust($api_request1) == 1) {
				$flag = true;
			} else if ($tw_account->getApi_Reqeust($api_request2) == 1) {
				$flag = true;
			} else if ($tw_account->getApi_Reqeust($api_request3) == 1) {
				$flag = true;
			}


		
			if ($flag == false) {
				var_dump("不正なアクセスです。");
				ErrorService::setMessage('・不正なアクセスです。');
			}
		

			//status更新
			$tw_account->setQueue_Id($this->auto_function_name, null);
			$tw_account->setAutoFunc($this->auto_function_name ,5);
			$tw_account->save();
			return $tw_account;

		} catch (\Exception $e) {
			Log::debug($e);
			var_dump("エラー発生：予期せぬエラーが起きました。");
			ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
			return;
		}
	}

}
