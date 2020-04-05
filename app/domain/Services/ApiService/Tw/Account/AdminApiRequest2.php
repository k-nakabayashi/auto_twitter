<?php
//Twitter Api操作の中央集権的トレイト

//機能は以下の通りです
//Twitter Api　にリクエスト送信
//制限判定・状態判定
//リクエスト上限カウンターの判定・インクリメント・更新
//メール送信

namespace App\Domain\Services\ApiService\Tw\Account;
use App\Jobs\ReleaseApiRestrictionJob;
use Log;
use App\Tw_Account;
use App\Domain\Services\MailService\NotificationMail;
use App\Facades\ErrorService;
use Illuminate\Support\Facades\Auth;

/**
 * TODO Auto-generated comment.
 */

trait AdminApiRequest2 {

	//$this->auto_function_name→target_accountもしくはmy_account
	
	public function getNo_Checks() {
		return ['statuses_update',];
	}
	//Twitterにリクエストを飛ばし、データをうけとるメソッド
	// $model2の使用する時は、複数のモデルを使用する場合。
	// 例えばターゲットのフレンドリストを取る場合、
	//自分のアカウントのモデルからアクセストークンを取得し、ターゲットのモデルからtwitterのuser_idを取得する必要がある
  public function accessTwitterAPI($api_request, $model, $request_method ,$request_url, $params_a, $model2= null) {

			Log::debug($request_url);
			$my_account = $model2 ===null ? $model : $model2;

	
			/**************************************************

			[GET users/show]のお試しプログラム

			認証方式: アクセストークン

			配布: SYNCER
			公式ドキュメント: https://dev.twitter.com/rest/reference/get/users/show
			日本語解説ページ: https://syncer.jp/Web/API/Twitter/REST_API/GET/users/show/

		**************************************************/

			// 設定
			$access_token = $my_account->oauth_token;		// アクセストークン
			$access_token_secret = $my_account->oauth_token_secret;

			Log::debug($access_token);
			Log::debug($access_token_secret);
			Log::debug(json_encode($params_a));
		

			// キーを作成する (URLエンコードする)
			$signature_key = rawurlencode( config("api.TW_API_SECRET") ) . '&' . rawurlencode( $access_token_secret ) ;

			// パラメータB (署名の材料用)
			$params_b = array(
				'oauth_token' => $access_token ,
				'oauth_consumer_key' => config("api.TW_API_KEY") ,
				'oauth_signature_method' => 'HMAC-SHA1' ,
				'oauth_timestamp' => time() ,
				'oauth_nonce' => microtime() ,
				'oauth_version' => '1.0' ,
			) ;

			// パラメータAとパラメータBを合成してパラメータCを作る
			$params_c = array_merge( $params_a , $params_b ) ;

			// 連想配列をアルファベット順に並び替える
			ksort( $params_c ) ;

			// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
			$request_params = http_build_query( $params_c , '' , '&' ) ;

			// 一部の文字列をフォロー
			$request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

			// 変換した文字列をURLエンコードする
			$request_params = rawurlencode( $request_params ) ;

			// リクエストメソッドをURLエンコードする
			// ここでは、URL末尾の[?]以下は付けないこと
			$encoded_request_method = rawurlencode( $request_method ) ;
		
			// リクエストURLをURLエンコードする
			$encoded_request_url = rawurlencode( $request_url ) ;
		
			// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
			$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

			// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
			$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

			// base64エンコードして、署名[$signature]が完成する
			$signature = base64_encode( $hash ) ;

			// パラメータの連想配列、[$params]に、作成した署名を加える
			$params_c['oauth_signature'] = $signature ;

			// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
			$header_params = http_build_query( $params_c , '' , ',' ) ;

			// リクエスト用のコンテキスト
			$context = array(
				'http' => array(
					'method' => $request_method , // リクエストメソッド
					'header' => array(			  // ヘッダー
						'Authorization: OAuth ' . $header_params ,
					) ,
				) ,
			) ;

			// パラメータがある場合、URLの末尾に追加
			if( $params_a ) {
				$request_url .= '?' . http_build_query( $params_a ) ;
				// $request_url .= '?@'.$screen_name;
			}

			// オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
		//	if( $params_a ) {
		//		$context['http']['content'] = http_build_query( $params_a ) ;
		//	}

			// cURLを使ってリクエスト
			// var_dump($request_url);
			// var_dump("~~~~~~".$screen_name);
			// return;
	

			$curl = curl_init() ;
			curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
			curl_setopt( $curl, CURLOPT_HEADER, 1 ) ; 
			curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;	// メソッド
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;	// 証明書の検証を行わない
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;	// curl_execの結果を文字列で返す
			curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ;	// ヘッダー
		//	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {		// GETの場合は不要
		//		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;	// リクエストボディ
		//	}
			curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;	// タイムアウトの秒数

			//実行
			$res1 = curl_exec( $curl ) ;
			$res2 = curl_getinfo( $curl ) ;
			curl_close( $curl ) ;


			// 取得したデータ
			$json = substr( $res1, $res2['header_size'] ) ;		// 取得したデータ(JSONなど)
			$header = substr( $res1, 0, $res2['header_size'] ) ;	// レスポンスヘッダー (検証に利用したい場合にどうぞ)

			// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
			// $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;
	
			
			$outputFilePath = "./tw_account.json";
			$jsonString = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
			$arr = json_decode($jsonString,true, JSON_UNESCAPED_UNICODE);
			$json = json_encode($arr,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			file_put_contents($outputFilePath, $json);
		
			// JSONをオブジェクトに変換
			// var_dump($json);
			$obj = json_decode( $json );
		
			// if ($api_request != "followers_list") {
			// 	Log::debug($json);
			// }

			return $this->check_Result(
				$api_request, $obj, $my_account,
				$this->auto_function_name, Auth::id()
			);
			// return $obj;

	}

		//APIリクエストの結果判定
	public function check_Result (
		$api_request, $obj, $my_account,
		$auto_function_name, $app_id
		) 
	{

		Log::debug('checkApi');
		// $obj = [
		// 	"errors" =>  [
		// 			[
		// 					"message" =>  "アカウントが凍結中",
		// 					"code" => 63,
		// 			]
		// 	]
		// ];
		// $obj = [
		// 	"errors" =>  [
		// 			[
		// 					"message" =>  "15分制限",
		// 					"code" => 88,
		// 			]
		// 	]
		// ];
		// $obj = json_decode(json_encode($obj));

		//上限判定
		if (isset($obj->errors)) {
			Log::debug('message');
			Log::debug("checkapi error ");

			if ($obj->errors[0]->code == 63 || $obj->errors[0]->code == 326) {
				Log::debug('凍結中');

				$this->freezeAutoFunc($auto_function_name, $my_account);
				ErrorService::setMessage("・アカウントが凍結されました。Twitterで解除申請を行なってください。");
				return $obj;
			}
			
			//すでにフォロー済み
			Log::debug(json_encode($obj->errors[0]->code));
			if ($obj->errors[0]->code == 160) {
				Log::debug($obj->errors[0]->message);

			} else if ($obj->errors[0]->code == 162) {
				Log::debug($obj->errors[0]->message);


			//APi制限のため、delayでもう一度起動させる必要がある
			} else if ($obj->errors[0]->code == 161) {
				Log::debug('制限24時間: Twiiterにフォロー制限されました。'.$api_request);
				Log::debug($obj->errors[0]->message);

				if (!in_array($api_request, self::getNo_Checks())) {
					$delay = now()->addMinutes(config('api.RESTART_QUEUE2'));
					$this->disapatch_RestartApi ($delay, $api_request, $my_account, $auto_function_name);
					ErrorService::setMessage("・制限24時間: Twiiterに制限されました");
				}

			} else if ($obj->errors[0]->code == 88) {
				Log::debug(' 制限15分: Twiiterにフォロー制限されました。'.$api_request);
				Log::debug($obj->errors[0]->message);
				if (!in_array($api_request, self::getNo_Checks())) {
					$delay = now()->addMinutes(config('api.RESTART_QUEUE')-1);
					$this->disapatch_RestartApi ($delay, $api_request, $my_account, $auto_function_name);
					ErrorService::setMessage("・制限15分: Twiiterに制限されました");
		
				}
			}
		}

		return $obj;
	}

	//制限されていた時に使用
	public function disapatch_RestartApi ($delay, $api_request, $my_account, $auto_function_name){

		//ApiRequestを制限
		Log::debug("dis  id ;".$my_account['tw_account_id']);
		$my_account = Tw_Account::find($my_account['tw_account_id']);
		// $my_account->setApi_Status($api_request, 2);//状態：再開中　unfollowとかぶるため不要
		$my_account->setAutoFunc($auto_function_name, 2);//状態：再開中
		$my_account->setApi_Reqeust($api_request, 0);//制限する
		$my_account->save();

		//再開Job　：　api制限を解除する
		Log::debug("release api delay : ".$delay);
		ReleaseApiRestrictionJob::dispatch($my_account, $api_request)
		// ->onQueue('restart_api_request')
		->delay($delay);

		//制限通知メール
		$this->restrictAutoFunc($auto_function_name, $my_account);

	}


	
	//以下は、メール関連です。
	public function finishAutoFunc($auto_function_name, $my_account)
	{
		Log::debug('自動機能完了：'.$auto_function_name);
		$this->initApi_Request();

		//完了通知メールを送信
		$this->sendMail($auto_function_name, 'finish', $my_account);
	}

	public function restrictAutoFunc($auto_function_name, $my_account)
	{
		//制限通知メールを送信
		$this->sendMail($auto_function_name, 'restrict', $my_account);
	}

	public function freezeAutoFunc($auto_function_name, $my_account)
	{
		//「凍結中」に変更し、
		//制限通知メールを送信
		$my_account->follow = 4;
		$my_account->unfollow = 4;
		$my_account->tweet = 4;
		$my_account->favorite = 4;
		$my_account->suspended = true;
		$my_account->save();
		$this->sendMail($auto_function_name, 'freeze', $my_account);
	}

	public function sendMail($auto_function_name, $pattern, $my_account)
	{
		$id = $my_account->app_id;
		$mail = new NotificationMail(
			$id, $my_account->getKey(),
			$auto_function_name, $pattern
		);
		$mail->__invoke();
	}


	//以下ユーティリティ
	public function getRealTime_Accunt () {
		// $model = Tw_Account::find($id);
		$model = Tw_Account::find($this->dto['my_tw_account']->getkey());
		return  $model;
	}

	public function initApi_Request()
	{
		# code...
		Log::debug('api status initialized');
		$my_account = $this->getRealTime_Accunt();
		$my_account->setApi_status($this->queue_name, 0);
		$my_account->setQueue_Id($this->auto_function_name, null);
		$my_account->setAutoFunc($this->auto_function_name, 0);

		if (!empty($this->queue_name2)) {
			$my_account->setApi_status($this->queue_name2, 0);
		}

		if (!empty($this->queue_name3)) {
			$my_account->setApi_status($this->queue_name3, 0);
		}
		
		$my_account->save();
		$this->delete();
	}
}