<?php

namespace App\Http\Controllers\Actions\ApiRequest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Facades\ErrorService;
//リクエストトークン取得用
use App\Domain\Services\ApiService\Tw\RequestToken;

class GetRequestToken extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(RequestToken $token_service)
    {

        
        try {
          
            $request_token = $token_service->intialAccess();
            return response()->json(['data' => $request_token, 'errors' => ErrorService::getMessage()]);

        } catch (\Exception $e) {
            var_dump($e);
            ErrorService::setMessage('エラー発生：予期せぬエラーが起きました。');
            return response()->json(['errors' => ErrorService::getMessage()]);
        }
    }
}
