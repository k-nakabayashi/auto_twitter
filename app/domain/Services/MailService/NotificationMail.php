<?php
//各種通知メールを送信します。
//AdminApiRequest内のsendMail()から使用されます。

namespace App\Domain\Services\MailService;

use App\Tw_Account;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AutoFuctionFinishMail;
use App\Mail\AutoFuctionRestrictMail;
use App\Mail\AutoFuctionFreezeMail;

use Log;
/**
 * TODO Auto-generated comment.
 */
class NotificationMail {

	public $user;
	public $tw_account;
	public $auto_function_name;
	public $pattern;
	public $tweet;
	/**
	 * TODO Auto-generated comment.
	 */
	public function __construct(
		$app_id, $tw_account_id,
		$auto_function_name, 
		$pattern,
		$tweet_id = null)
	{
		Log::debug('mail set');
		//どの自動機能が完了したか？
		$this->auto_function_name = $auto_function_name;

		//ユーザー情報
		$this->user = User::find($app_id);
		$this->tw_account = Tw_Account::find($tw_account_id);

		//mailのパターン
		$this->pattern = $pattern;

		$this->tweet_id = $tweet_id;
	}

	public function __invoke() {

		$mailer = "";

		switch ($this->pattern) {
			case 'finish':
				$mailer = new AutoFuctionFinishMail($this->user, $this->tw_account, $this->auto_function_name);
				break;
			case 'restrict':
				$mailer = new AutoFuctionRestrictMail($this->user, $this->tw_account, $this->auto_function_name);
				break;
			case 'freeze':
				$mailer = new AutoFuctionFreezeMail($this->user, $this->tw_account);
			break;
		}

		Mail::to($this->user->email)->send($mailer);

	}
}
