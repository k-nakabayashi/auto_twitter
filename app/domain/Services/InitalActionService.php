<?php
//・ツイッターアカウント登録
//初期画面表示で必要なパラメータinitial_dataを画面にreturn

namespace App\Domain\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Tw_Account;
use App\ApiCounter;

use Log;
use App\Domain\Services\ApiService\Tw\RequestToken;
use App\Facades\ErrorService;
use App\Domain\Services\ApiService\Tw\Account\AdminApiRequest2;

trait InitalActionService {
    use AdminApiRequest2;



    //ツイッターアカウント登録
    //重複・アカウント10超え・エラーを補足します。
    public function initialRender (
        Request $req, 
        RequestToken $req_token, 
        $page='home'
        ) 
    {

        $initial_data = [
            'tw_return_api_flg' => false,
            'tw_duplication' => false,
            'restricted_creating' => false,
        ];
       

        $settoken_query = $req_token->collback_to_app($req);
        if ($settoken_query === true) {
            Log::debug('ホームへ');
            return view('home', ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);
        }

        //認証失敗
        if ($settoken_query === null) {

            if(empty(request('id'))){

                ErrorService::setMessage('予期せぬエラー発生');
                return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);
            }

            return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);    
        }

        //アクセストークンを使ってアカウント登録する
        //認証しようとしているアカウントの情報を持っているかを確認
        if (isset($settoken_query['user_id'])) {
            // //DBに追加
            Log::debug("home crtl , auth ok ");
            Log::debug($settoken_query);
            $duplication_chk = tw_account::where("user_id", $settoken_query["user_id"])->first();

 
            if (isset($duplication_chk)) {
                //すでに登録済みの場合

                $initial_data['tw_return_api_flg'] = true;
                ErrorService::setMessage('そのTwitterアカウントはすでに登録済みです。');
                return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);
                
            } 
            // DB::beginTransaction();
            
            try {

                $my_tw_account_list = Tw_Account::select('tw_account_id')->where("deleted_at", null)
                ->where('app_id', Auth::id())
                ->orderBy('created_at')
                ->get()->toArray();
                $count =  count($my_tw_account_list);

                if ($count >= 10) {
                    $initial_data['restricted_creating'] = true;
                    return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);
                }

                //DBにレコード追加準備
                $tw_account = app('App\Tw_Account');
                $tw_account->app_id = Auth::id();
                $tw_account->user_id = $settoken_query['user_id'];
                $tw_account->oauth_token = $settoken_query['oauth_token'];
                $tw_account->oauth_token_secret = $settoken_query['oauth_token_secret'];
                $tw_account->screen_name = $settoken_query['screen_name'];
                // $tw_account->save();

                $request_method = 'GET' ;
                $params = array(
                    "user_id" => $settoken_query['user_id'],
                    "include_entities" => "false",
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
                    return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);
                }

                $tw_account->profile_image_url_https = $result->profile_image_url_https;
                $tw_account->name = $result->name;
                $tw_account->description = $result->description;
                $tw_account->save();


                //カウンターの重複チェック
                if(self::initApi_Counter($tw_account)) {
                    Log::debug('カウンター重複');
                    return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);
                };

                $tw_account->setApi_Status("users_show", 0);//動作完了したので停止
                $initial_data['tw_return_api_flg'] = true;
                return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);

            } catch (\Exception $e) {
                // DB::rollback();
                Log::debug(($e));
                return view($page, ['initial_data' => json_encode($initial_data), 'errors' => ErrorService::getMessage()]);
            }
        }


    }



    public static function initApi_Counter($tw_account)
    {
        
        //Api_counter登録
        Log::debug('api request create');
        $api_counter_model = new ApiCounter();

        //重複チェック
        $query = $api_counter_model->query();
        $no_duplication = array_keys($api_counter_model->no_duplication);
        foreach ($no_duplication as $column) {
            $query->where($column, $column);
        }
        $check = count($query->get()->toArray());
        
        if ($check > 0) {
            ErrorService::setMessage('予期せぬエラー発生');
            return true;
        }

        $request_list = $api_counter_model->request_list;
        $data = "";
        $id = 0;
        Log::debug("カウンター作成開始");
        Log::debug(json_encode($request_list));
        foreach (array_keys($request_list) as $requst)  {
           
            $data = [
                'counting_started_at' => date("Y/m/d"),
                'request' => $requst,
                'max_daily_counter' => $request_list[$requst],
                'tw_account_id' => $tw_account->getKey(),
                'created_at' => date("Y/m/d H:i:s"),
                'updated_at' => date("Y/m/d H:i:s"),

            ];

            $id = DB::table($api_counter_model->getTable())->insertGetId($data);
            Log::debug("$requst  id :  $id");
            $tw_account->setApi_Counter($requst ,$id);
        }
        $tw_account->save();
        return false;
    }
}