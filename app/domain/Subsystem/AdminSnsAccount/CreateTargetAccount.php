<?php
//1.Requestから自分のtw_Account_idをアクセストークンをゲット
//2.Twitterからターゲットアカウントデータ取得
//移譲先で、スクリーンネームをRequestから取得
//3.ターゲットアカウント保存

namespace App\Domain\Subsystem\AdminSnsAccount;
use Log;
use App\Facades\ErrorService;
use App\Tw_Account;
use Illuminate\Support\Facades\Auth;

class CreateTargetAccount {

  public $request;
  private $service;

  public function __construct()
  {
    $this->request = app('Illuminate\Http\Request');
    $this->service = app('App\Domain\Services\ApiService\Tw\Account\GetTargetAccount');

  }
  
  public function __invoke() {

    //1.Requestから自分のtw_Account_idを取得して色々ゲット
    $input = $this->request;
    if (count($input->all()) <= 0) {
      Log::debug("エラー発生：必要なパラメータが存在しません");
      ErrorService::setMessage("・エラー発生：必要なパラメータが存在しません");
      return;
    }

    // $tw_account_model = app('App\Tw_Account');
    // $id = $input->input($tw_account_model->getKeyName());
    
    //アクセストークン取得で使う
    $my_tw_account = Tw_Account::where(['suspended'=>false,'deleted_at'=>null, 'app_id'=>Auth::id()])->first();
    if ($my_tw_account == null) {
      ErrorService::setMessage("・利用できるアカウントがありません。");
      ErrorService::setMessage("・新しくアカウントを登録、もしくは");
      ErrorService::setMessage("・Twitterにアカウント解除申請を行なってください。");
      return;
    }
    try {

      //2.Twietterからターゲットアカウントデータ取得
      $screen_name = $this->request->input("screen_name");
      $tw_target_account_model = app('App\Tw_Target_Account');//fill saveで使う

      //移譲先でターゲットアカウントの情報を取得
      $target_account = $this->service->__invoke($my_tw_account, $screen_name);
      if ($target_account == null) {
        return;
      }
      // //3.ターゲットアカウント保存
      $check = $tw_target_account_model
      ->where([
        "target_account_user_id"=>$target_account["target_account_user_id"],
        "app_id" => $target_account["app_id"]
      ])->first();

      if (empty($check)) {

        $tw_target_account_model->fill($target_account)->save();

      } else {

        Log::debug("すでにそのターゲットアカウントは登録済みです");
        ErrorService::setMessage("・すでにそのターゲットアカウントは登録済みです");
        return;
      }
      
      //ターゲットアカウントをreturn;
      $target_account_data = $tw_target_account_model->toArray();
      return $target_account_data;

    } catch (\Exception $e) {
      Log::debug($e);
      Log::debug("エラー発生：予期せぬエラーが起きました。");
      ErrorService::setMessage("・エラー発生：予期せぬエラーが起きました。");
      return;
    }

  }
}
