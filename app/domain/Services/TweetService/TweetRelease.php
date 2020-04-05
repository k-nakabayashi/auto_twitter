<?php
//凍結からの復旧

namespace App\Domain\Services\TweetService;
use Illuminate\Support\Facades\Request;
use App\Tw_Account;
use App\Facades\ErrorService;
use Log;

use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\SuspendChecker;
use App\Tw_Auto_Tweet;
/**
 * TODO Auto-generated comment.
 */
class TweetRelease implements AutoFunctionInterface{
	use SuspendChecker;

	public $model;
	public $auto_function_name = 'tweet';
	public function __construct()
	{

		$this->model = app('App\Tw_Account');
		
	}
	/**
	 * TODO Auto-generated comment.
	 * $model : tw_accout
	 */
	public function __invoke() {

		try {
			Log::debug('restart service : Tweeet Release');

      $id = Request::input('id');
      $auto_tweet = Tw_Auto_Tweet::find($id);
      $tw_account_id = $auto_tweet->tw_account_id;

						
			if (Tw_Account::find($tw_account_id)->getAutoFunc($this->auto_function_name) != 4) 
			{
		
				ErrorService::setMessage('・不正なアクセスです');
				return;
			}

			//凍結からの再開判定
			if ($this->start($tw_account_id)) 
			{		
				//失敗
				return;
			}

			$tw_account = Tw_Account::find($tw_account_id);
			$tw_account->setAutoFunc($this->auto_function_name, 0);
			$tw_account->save();

			return 0;


		} catch (\Exception $e) {
			Log::debug($e);
			var_dump("エラー発生：予期せぬエラーが起きました。");
			ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
			return;
		}

	}

}
