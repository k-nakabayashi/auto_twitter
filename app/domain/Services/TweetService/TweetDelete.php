<?php
namespace App\Domain\Services\TweetService;
use Log;
use App\Domain\Services\AutoFunctionInterface;
use App\Facades\ErrorService;
use App\Tw_Account;
use App\Tw_Auto_Tweet;

/**
 * TODO Auto-generated comment.
 */
class TweetDelete implements AutoFunctionInterface{

	public $auto_function_name;
	public $req;
	public function __construct()
	{
		$this->req = app("Illuminate\Http\Request");
		
		if (!empty($this->req->input("domain4"))) {
			$this->auto_function_name = $this->req->input("domain4");//tweet
		}
	}
	/**
	 * TODO Auto-generated comment.
	 * $model : tw_accout
	 */
	public function __invoke() {

		try {
			Log::debug('tweet delete');

      $id = $this->req->input('id');
      $auto_tweet = Tw_Auto_Tweet::find($id);
      $tw_account = Tw_Account::find($auto_tweet->tw_account_id);
			
			if ($tw_account->suspended == true) {
				ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
				return true;
			}

			if ($tw_account->getAutoFunc($this->auto_function_name) == 4) {
				Log::debug("投稿済みなので削除できません。");
				ErrorService::setMessage('投稿済みなので削除できません。');
				return;
			}

			if ($auto_tweet->tweet_status == 0 ) {
				$auto_tweet->delete();
				$auto_tweet->save();
				return $tw_account;
			}

			$auto_tweet->tweet_status = 5;
			$auto_tweet->save();
			return $tw_account;

		} catch (\Exception $e) {
			Log::debug($e);
			var_dump("エラー発生：予期せぬエラーが起きました。");
			ErrorService::setMessage('・エラー発生：予期せぬエラーが起きました。');
			return;
		}
	}

}
