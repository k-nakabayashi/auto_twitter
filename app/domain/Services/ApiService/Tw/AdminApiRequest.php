<?php
//Twitter Api操作の中央集権的トレイト

//機能は以下の通りです
//Twitter Api　にリクエスト送信
//制限判定・状態判定
//リクエスト上限カウンターの判定・インクリメント・更新
//メール送信

//判定前→自動機能1, リクエスト状態1、制限1という判定になります。
//制限されると→自動機能2, 各種リクエスト状態2、制限0という判定になります。※自動的に再開Jobが発行されますので、再開中「2」となります。
//凍結されると→自動機能4, 各種リクエスト状態3、制限1という判定になります。※リクエスト状態3への更新は、CheckApiRequestの中で行なっております。

namespace App\Domain\Services\ApiService\Tw;
use App\Jobs\ReleaseApiRestrictionJob;
use Log;
use App\ApiCounter;
use Carbon\Carbon;
use App\Tw_Account;
use App\Domain\Services\MailService\NotificationMail;
/**
 * TODO Auto-generated comment.
 */

trait AdminApiRequest {

	
	public function getNo_Checks() {
		return ['test',];
		return ['statuses_update',];
	}
	//Twitterにリクエストを飛ばし、データをうけとるメソッド
	// $model2の使用する時は、複数のモデルを使用する場合。
	// 例えばターゲットのフレンドリストを取る場合、
	//自分のアカウントのモデルからアクセストークンを取得し、ターゲットのモデルからtwitterのuser_idを取得する必要がある
  public function accessTwitterAPI($api_request, $model, $request_method ,$request_url, $params_a, $model2= null) {

			Log::debug($request_url);
			$my_account = $model2 ===null ? $model : $model2;

			//一時停止中の場合
			if ($my_account->getApi_status($api_request) == 5) {
				Log::debug('一時停止中：リクエストせずにjob fail');
				return;
			}
			//凍結判定
			if($my_account->suspended == true) {

				Log::debug('凍結中：リクエストせずにjob fail');

				$obj = [
					"errors" =>  [
							[
									"message" =>  "アカウントが凍結中",
									"code" => 63,
							]
					]
				];
				return json_decode(json_encode($obj));
			}

			if (!in_array($api_request, self::getNo_Checks() )) {
				//カウンターはアカウント毎に紐ついている。
				Log::debug('リクエスト前にAPI制限・状態判定を開始します。');
				$counter_id = $my_account->getApi_Counter($api_request);
				$api_counter = ApiCounter::find($counter_id);

				//カウンターの初期化
				$this->initDailyCounter($api_counter);
				
				//アプリ側で設定した制限判定
				if($this->haveRestricted($api_counter) == true) {

					Log::debug('制限24時間：設定した制限に達したため制限されました。');

					$obj = [
						"errors" =>  [
								[
										"message" =>  "制限24時間：すでに制限されてます。このリクエストは受けつけできません",
										"code" => config('api.TW_RESTRINCION_CODE2'),
								]
						]
					];
					return json_decode(json_encode($obj));;
				}

				if($this->checkApi_counter($api_counter, $api_request,$my_account, $this->auto_function_name) == true) {
					Log::debug('制限されたか？');
					Log::debug('制限24時間：設定した制限に達したため制限されました。');

					$obj = [
						"errors" =>  [
								[
										"message" =>  "制限24時間：設定した制限に達したため制限されました。",
										"code" => config('api.TW_RESTRINCION_CODE'),
								]
						]
					];
					return json_decode(json_encode($obj));;
				}
			}
			Log::debug('accese: 制限判定を通過しました。');
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
				$this->auto_function_name, $this->getRealTime_Accunt()->app_id
			);
			// return $obj;

	}

	//デイリー制限を超えなかった時  ok
	public function initDailyCounter($counter_model) {

		Log::debug('initDailyCounter');

		//アカウント未登録の場合		
		if ($counter_model == null) {
			return;
		}

		$now_date = new Carbon(date('Y/m/d'));
		$target = new Carbon($counter_model->counting_started_at);
		$counting_started_at = $target->format('Y/m/d');

		Log::debug($counter_model->max_daily_counter != 0);
		Log::debug($counter_model->counter <= $counter_model->max_daily_counter );
		Log::debug($now_date);
		Log::debug($counting_started_at);
		Log::debug($now_date->gt(new Carbon($counting_started_at)));
		if (
				$counter_model->max_daily_counter != 0 && 
				$counter_model->counter <= $counter_model->max_daily_counter &&
				$now_date->gt(new Carbon($counting_started_at))
			) 
		{
			 	Log::debug('coutner 初期化します');
				$counter_model->counting_started_at = $now_date;
				$counter_model->counter = 0;
				$counter_model->save();
		}
	}

	//すでに制限されているかの確認 ok
	public function haveRestricted($counter_model)
	{
		Log::debug('haveRestricted');

		//アカウント未登録の場合		
		if ($counter_model == null) {
			return false;
		}
	
		$counting_started_at = new Carbon($counter_model->counting_started_at);
		$now_date = new Carbon(date('Y/m/d H:i:s'));

		//更新されてるからすでに制限済み
		if ($counter_model->max_daily_counter !=0 && $counting_started_at->gt($now_date)){
			Log::debug('すでに制限済み');
			return true;
		}

		return false;
	}
	
	//tw_acountとapi_request
	//アプリ側で設定した制限で判定 ok
	public function checkApi_counter($counter_model, $api_request, $my_account, $auto_function_name)
	{
		Log::debug('checkApi_counter');

		//アカウント未登録の場合		
		if ($counter_model == null) {
			return false;
		}

		$counting_started_at = new Carbon($counter_model->counting_started_at);
		$now_date = new Carbon(date('Y/m/d H:i:s'));
		Log::debug('日付判定確認 : '.$now_date->gt($counting_started_at));


		//制限判定開始
		if (
			$counter_model->max_daily_counter != 0 && 
			$now_date->gt($counting_started_at)) 
		{

			Log::debug($counting_started_at);
			Log::debug($now_date);

			//日付判定
			//デイリー制限を超えたた時    
			if($counter_model->counter >= $counter_model->max_daily_counter) {
				Log::debug('カウンター更新：制限');
				//次の起動時間を設定

				//api_counterのcounting_started_at更新
				$tomorrow = 'Y/m/'.(string)(((int) date('d')) + 1);
				$next_date = date($tomorrow);
				$counter_model->counting_started_at = date($next_date);
				$counter_model->save();

				//restartJobのdispatch
				$next_date = new Carbon($next_date);
				$delay_for_next_start = $next_date->diffInMinutes($now_date);

				$delay = now()->addMinutes($delay_for_next_start);
				Log::debug('カウンター内');
				Log::debug("next_date : ".$next_date);
				Log::debug("now_date : ".$now_date);
				Log::debug("delay_for_next_start : ".$delay_for_next_start);

				$this->disapatch_RestartApi($delay, $api_request, $my_account, $auto_function_name);

				return true;

			}
			$counter_model->counter ++;
			$counter_model->save();
		} 


		return false;

	}

	public function disapatch_RestartApi ($delay, $api_request, $my_account, $auto_function_name){

		//ApiRequestを制限
		Log::debug("dis  id ;".$my_account['tw_account_id']);
		$my_account = Tw_Account::find($my_account['tw_account_id']);

		$my_account->setApi_Status($api_request, 2);//状態：再開中
		$my_account->setApi_Reqeust($api_request, 0);//制限する
		$my_account->setAutoFunc($auto_function_name, 2);//状態：再開中
		$my_account->save();

		//再開Job　：　api制限を解除する
		Log::debug("release api delay : ".$delay);
		ReleaseApiRestrictionJob::dispatch($my_account, $api_request)
		// ->onQueue('restart_api_request'.$my_account->queue_name)
		->delay($delay);

		//制限通知メール
		$this->restrictAutoFunc($auto_function_name, $my_account);

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
		$obj = json_decode(json_encode($obj));

		//上限判定
		if (isset($obj->errors)) {
			Log::debug('message');
			Log::debug("checkapi error ");
			Log::debug(json_encode($obj->errors));
			if ($obj->errors[0]->code == 187) {
				return true;
			}
			if ($obj->errors[0]->code == 63 || $obj->errors[0]->code == 326) {
				Log::debug('凍結中');

				$this->freezeAutoFunc($auto_function_name, $my_account,$api_request);
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
		
				}

			} else if ($obj->errors[0]->code == 88) {
				Log::debug(' 制限15分: Twiiterにフォロー制限されました。'.$api_request);
				Log::debug($obj->errors[0]->message);
				if (!in_array($api_request, self::getNo_Checks())) {
					$delay = now()->addMinutes(config('api.RESTART_QUEUE')-1);
					$this->disapatch_RestartApi ($delay, $api_request, $my_account, $auto_function_name);
				
		
				}
			}
		}

		return $obj;
	}

	//Job→Jobとまたがる動きをする時に使います。	
	public function setFinishAndStartForApiStatus(
		$finish, //停止状態「0」となる
		$start// 起動状態「1」となる
	)
	{
		$my_account = $this->getRealTime_Accunt();
		$my_account->setApi_status($finish, 0);
		$my_account->setApi_status($start, 1);
		$my_account->save();
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
	
	//以下は、メール関連です。
	public function finishAutoFunc($auto_function_name, $my_account)
	{
		Log::debug('自動機能完了：'.$auto_function_name);
		if ($auto_function_name == 'tweet') {
			//完了通知メールを送信
			$this->sendMail($auto_function_name, 'finish', $my_account);
			return;
		}

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
		if ($my_account->unfollow_flag == true) {
			//アンフォローできるアカウントだけ。
			$my_account->unfollow = 4;
		}
		$my_account->follow = 4;
		$my_account->tweet = 4;
		$my_account->favorite = 4;
		$my_account->suspended = true;
		$my_account->save();
		$this->sendMail($auto_function_name, 'freeze', $my_account);
	}

	public function sendMail($auto_function_name, $pattern, $my_account)
	{
		$id = $my_account->app_id;
		$tweet_id = null;
		if (isset($this->dto['tweet_id'])) {
			$tweet_id = $this->dto['tweet_id'];
		}
		$mail = new NotificationMail(
			$id, $my_account->getKey(),
			$auto_function_name, $pattern, $tweet_id
		);
		$mail->__invoke();
	}


	public function getRealTime_Accunt () {
		// $model = Tw_Account::find($id);
		$model = Tw_Account::find($this->dto['my_tw_account']->getkey());
		return  $model;
	}

}