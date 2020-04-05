<?php
//機能再開アクションで使います。
//再開する際に、再開可能かの判定を行なっております。

namespace App\Domain\Services;
use App\Facades\ErrorService;
use App\Tw_Account;
use Log;

trait SuspendChecker{
  
  public function start ($id)
  {
 
    Log::debug('SuspendChecker');
    Log::debug($id);
    $tw_account = Tw_Account::find($id);

    $request_method = 'GET' ;
		$params = array(
			"user_id" => $tw_account->user_id,
			"include_entities" => true,
		);
    $request_url = 'https://api.twitter.com/1.1/users/show.json';
    
		$result = $this->accessTwitterAPI(
			'users_show',
			$tw_account,
			$request_method, 
			$request_url, 
			$params
    );
    
    if (isset($result->errors)) {
      Log::debug('has errors');
      ErrorService::setMessage('・エラー発生');
      return true;
    }
    if ($result->suspended == true) {
      Log::debug('freezed');
      ErrorService::setMessage('・アカウント凍結中：Twitterにて凍結解除を行なってください。');
      return true;
    }

    Log::debug('アカウント使用可能');
    Log::debug('suspended : falseに');
    $my_tw_account = Tw_Account::find($id);
    $my_tw_account->suspended = false;
    $my_tw_account->save();

    return false;
  }

  public static function accessTwitterAPI($api_request, $model, $request_method ,$request_url, $params_a, $model2= null) {

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

    return $obj;
      // return $obj;

  }

}
?>