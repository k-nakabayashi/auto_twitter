<?php
//自動「フォロー、アンフォロー、いいね、ツイート」機能のアクション
//ツイート予約だけは、このファイルを使わず、TweetStartAction.phpを使います。

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Services\AutoFunctionInterface;

use App\Facades\ErrorService;
use Illuminate\Validation\Factory;
use App\Tw_Account;
use App\Tw_Auto_Tweet;
use Log;

class AutoFunctionController extends Controller
{
    /**
   * TODO Auto-generated comment.
   */
  public $req;
  public $factory;
  public $domain;
  public function __construct(Request $req, Factory $factory, AutoFunctionInterface $domain)
  {

    $this->req = $req;
    $this->factory = $factory;
    $this->domain = $domain;
    Log::debug($this->req->all());
    //autofunction
  }
 
  //自動機能開始
  public function start() {

    $model = $this->domain->__invoke();
    return response()->json(['data' => $model, 'errors' => ErrorService::getMessage()]);
  }

  //自動機能　開始、停止　再開
  public function edit(Tw_Account $model, Tw_Auto_Tweet $tweet) {
    //ドメイン側で
    //リクエスト状態を「再開中」にし
    //再開Jobを発行する

    $validator = "";
    if (!empty($this->req->input('domain4'))) {
      $validator = $this->factory->make($this->req->all(), $tweet->getColums_for_Edit());
    } else {
      $validator = $this->factory->make($this->req->all(), $model->getColums_for_Edit());
    }

    if ($validator->fails()) {
        ErrorService::setMessage('・選択不足の項目があります。');
        return response()->json(['errors' => ErrorService::getMessage()]);
    }
    $tw_account = $this->domain->__invoke();
    return response()->json(['data' => $tw_account, 'errors' => ErrorService::getMessage() ]);
  }

  //自動機能 復旧
  public function release(Tw_Account $model, Tw_Auto_Tweet $tweet) {
    //ドメイン側で
    //リクエスト状態を「再開中」にし
    //再開Jobを発行する
    
    $validator = "";
    $tw_account_id = "";

    if (!empty($this->req->input('domain4'))) {
      
      Log::debug('111');
      $validator = $this->factory->make($this->req->all(), $tweet->getColums_for_Edit());
      $id = $this->req->input('id');
      $tw_account_id = Tw_Auto_Tweet::find($id)->tw_account_id;

    } else {
      
      Log::debug('222');
      $validator = $this->factory->make($this->req->all(), $model->getColums_for_Edit());
      $tw_account_id = $this->req->input('tw_account_id');
    
    }

    if ($validator->fails()) {
        ErrorService::setMessage('・選択不足の項目があります。');
        return response()->json(['errors' => ErrorService::getMessage()]);
    }

    if (Tw_Account::find($tw_account_id)->suspend == true) {

      ErrorService::setMessage('・不正さなアクセス：アカウントの凍結解除通知を行なってください。');
      return response()->json(['errors' => ErrorService::getMessage()]);
    }

    $next_btn = $this->domain->__invoke();
    
    $tw_account = Tw_Account::find($tw_account_id);
    return response()->json(['data' => $tw_account, 'next_btn' => $next_btn, 'errors' => ErrorService::getMessage() ]);
  }
}
