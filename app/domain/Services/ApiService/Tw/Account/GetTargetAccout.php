<?php
//ターゲトアカウントを登録します

namespace App\Domain\Services\ApiService\Tw\Account;
use Illuminate\Support\Facades\Auth;
use Log;
use App\Facades\ErrorService;

/**
 * TODO Auto-generated comment.
 */

class  GetTargetAccount {
	use AdminApiRequest2;

	public $auto_function_name = "target_account"; //通知メールで使います。
	public $dto;

  public  function __invoke($my_tw_account, $screen_name)
  {

		$this->dto['my_tw_account'] = $my_tw_account;

		$request_method = 'GET' ;
		$params = array(
			"screen_name" => $screen_name,
			"include_entities" => false,
		);
		$request_url = 'https://api.twitter.com/1.1/users/show.json';


		$tw_account_FromTw = $this->accessTwitterAPI(
			'users_show',
			$my_tw_account, 
			$request_method, 
			$request_url, 
			$params
		);

		if (isset($tw_account_FromTw->errors)) {
			if ($tw_account_FromTw->errors[0]->code = 50) {
				Log::debug('指定のスクリーンネームではユーザが見つかりません。');
				ErrorService::setMessage("・指定のスクリーンネームではユーザが見つかりません。");
			}
			return;
		}
		//公開判定
		if (isset($tw_account_FromTw->protected)) {
			if ($tw_account_FromTw->protected == true) {
				ErrorService::setMessage("・$tw_account_FromTw->name さんは非公開アカウントのため、ターゲット対象にできません。");
				return;
			}
			//プロフィールに日本語が含まれない場合は即リターン
			if (!preg_match('/[一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[ａ-ｚＡ-Ｚ０-９]+/u',$tw_account_FromTw->description)) {
				Log::debug("プロフィールに日本語が使われていないため、登録拒否します。");
				ErrorService::setMessage("・$tw_account_FromTw->name さんのプロフィールに日本語が使われていないため、登録拒否します。");
				return;
			}
		}

		//保存するために整形してリターン
		$obj = [
			'target_account_user_id' => $tw_account_FromTw->id,

			'name' => $tw_account_FromTw->name,
			'screen_name' => $tw_account_FromTw->screen_name,
			'followers_count' => $tw_account_FromTw->followers_count,
			'friends_count' => $tw_account_FromTw->friends_count,
			'description' => $tw_account_FromTw->description,
			'profile_image_url_https' => $tw_account_FromTw->profile_image_url_https,
			
			"app_id" => Auth::id(),
		];
		Log::debug("tw ターゲットカウント");
		Log::debug(json_encode($tw_account_FromTw,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		Log::debug("obj ターゲットカウント");
		Log::debug(json_encode($obj,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    return $obj;
  }

}
