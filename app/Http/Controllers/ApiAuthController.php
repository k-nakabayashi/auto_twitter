<?php
//ツイッターアカウント登録で使います。


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Services\ApiService\Tw\RequestToken;


use App\Domain\Services\InitalActionService;

// use Log;
class ApiAuthController extends Controller
{
    use InitalActionService;
    public $auto_function_name = "my_account"; //通知メールで使います。
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $req, RequestToken $req_token)
    {
        return $this->registerTwitterAccount($req, $req_token);
    }

}