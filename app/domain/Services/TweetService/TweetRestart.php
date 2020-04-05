<?php
//凍結中に投稿Jobが実行されると、failする
//そのjobを再発行します。
//もし凍結中にJob未実行ですと、failしていないので再発行の対象外です。

namespace App\Domain\Services\TweetService;
use Illuminate\Support\Facades\Request;
use App\Tw_Account;
use Illuminate\Support\Facades\DB;
use App\Jobs\RestartApiJob;
use App\Facades\ErrorService;
use Log;

use App\Domain\Services\AutoFunctionInterface;
use App\Domain\Services\SuspendChecker;
use App\Tw_Auto_Tweet;
/**
 * TODO Auto-generated comment.
 */
class TweetRestart implements AutoFunctionInterface{
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
		Log::debug('restart service : TweetwRestart');

		$id = Request::input('id');
		$auto_tweet = Tw_Auto_Tweet::find($id);
		$tw_account = Tw_Account::find($auto_tweet->tw_account_id);

		if ($tw_account->suspend == true) {
			//普通に起動する際に、凍結フラグを判定
			ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
			return;
		}

		// if ($tw_account->getAutoFunc($this->auto_function_name) != 3) {
		// 	Log::debug("予期せぬエラー：再開できる状態ではありません");
		// 	ErrorService::setMessage('予期せぬエラー：再開できる状態ではありません');
		// 	return;
		// }

		if ($auto_tweet->tweet_status != 3) {
			Log::debug("予期せぬエラー：再開できる状態ではありません");
			ErrorService::setMessage('予期せぬエラー：再開できる状態ではありません');
			return;
		}

		//対象のfailed_jobを抽出
		$api_request = Request::input('queue_name1');//statuses_update
		$queue_id = $auto_tweet->queue_id;
		$failed_queue = DB::table('failed_jobs')->where('queue_id', $queue_id)->first();


		//再開Job発行
		//failed_queueの有無を確認
		if ($failed_queue) {

			Log::debug('ツイート投稿JIb 再発行開始');

			RestartApiJob::dispatch($tw_account->getKey(), $failed_queue->id, $api_request, $this->auto_function_name, $auto_tweet->getKey())
			->delay(now()->addMinutes(config('api.RESTART_QUEUE')));
			$auto_tweet->tweet_status = 2;
			$auto_tweet->save();
			
			return $tw_account;
		}

		

		Log::debug("まだ「再開」可能な状態ではありません。少々お待ちください。");
		ErrorService::setMessage('まだ「再開」可能な状態ではありません。少々お待ちください。');
		return;


	}

}
