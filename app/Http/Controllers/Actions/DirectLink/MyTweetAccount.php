<?php

namespace App\Http\Controllers\Actions\DirectLink;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Services\ApiService\Tw\RequestToken;
use App\Domain\Services\InitalActionService;
use App\Facades\ErrorService;
use Illuminate\Support\Facades\Auth;
use App\Tw_Account;
use Log;
class MyTweetAccount extends Controller
{
    use InitalActionService;
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $req)
    {
        Log::debug('direct acc');   
        Log::debug($req->input("id"));
        if (empty($req->input("id"))) {
            Log::debug("不適切なアクセスです");
            ErrorService::setMessage('不適切なアクセスです');
            return redirect('home');
        }
        
        $check = Auth::id();
        $tw_account_data = Tw_Account::find($req->input("id"));
        if (empty($tw_account_data)) {
            Log::debug("不正なアクセスです");
            ErrorService::setMessage('不正なアクセスです');
            return redirect('home');
        }
        if ($check != $tw_account_data->app_id) {
            Log::debug("不正なアクセスです");
            ErrorService::setMessage('不正なアクセスです');
            return redirect('home');
        }
        Log::debug("direcet id : ".$tw_account_data->getKey());
        return redirect('home');
        //詳細ページに飛ぶとVueライフサイクルにひっかからないので現在コメントアウト
        // return view('my_tw_account')->with(['data' => $tw_account_data->getKey(), 'errors' => ErrorService::getMessage()]);
    }
}
