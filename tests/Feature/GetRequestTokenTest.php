<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

//通信
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//DB
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Tw_Account;

//テスト
use Log;
use Mockery;
use Illuminate\Support\Str;

class GetRequestTokenTest extends TestCase
{
    protected $user_id = 7;
    protected $screen_name = "pajmwa";
    protected $action_class_name = "createTarget";
    protected static $test_model_list = array();
    protected static $model_info;
    protected $user;

    public static function setUpBeforeClass()
    {
  
        $model_names = preg_replace('#/#', "\\", glob('App/*.php'));
        $model_names = preg_replace('#.php#', "", $model_names);

        foreach ($model_names as $model_name) {

            $test_model_ob = app($model_name);
        
            $test_model = [
                "obj" => $test_model_ob,
                "model_name" =>  $model_name, 
                "table_name"=> $test_model_ob->getTable(),
                "pk"=> $test_model_ob->getKeyName(),
                "select_columns_for_show" => $test_model_ob->select_columns_for_show,
                "select_columns_for_create" => $test_model_ob->select_columns_for_create,
                "query_for_edit" => $test_model_ob->query_for_edit,
                "select_columns_for_check_edtion" => $test_model_ob->select_columns_for_check_edtion,
                "param" => $test_model_ob->param,
            ];
           self::$test_model_list[$model_name] = $test_model;
        }

        // self::$model_info = self::$test_model_list["App\Tw_Api_Request"];
        self::$model_info = self::$test_model_list["App\Tw_Account"];       
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::find($this->user_id);
        Session::start();
        //選択したユーザ一人が存在するかチェック
        $data = $this->user->toArray();
        $this->assertDatabaseHas('users', $data);


        //ログイン
        $this->post('/login', 
        [
            'email' => $this->user->email,
            'password' => 'password',
            '_token' => csrf_token(), 
        ]
        )->assertStatus(302);
        
        $this->assertAuthenticatedAs($this->user);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    //エンドポイント確認テストok
    //ドメイン接続確認テストok
    //各種サービス接続確認テスト
      //1.ターゲットアカウントデータ取得
      //接続OK
      //APIリクエスト　パラメータ準備OK
      //request

    //Request値のとれてるか確認テスト
    //DB操作確認テスト
    //戻り値はなし、statusのみ確認
    //Que発行確認テスト
    //Que実行確認テスト
    
    //ターゲットフォロワーリスト作成テスト

    //最後にキューから外部APIテスト

    /**
     * A basic feature test example.
     *
     * @return void
     */

     
    public function testEndpoint()
    {
        $start_time = time();

        //screen_name→ターゲット検索のため
        $this->actingAs($this->user);


        // //tw_account_id→外部API操作用のトークンやテーブル保存のため
        $id  = $this->getID_List();
        echo self::$model_info['pk']." : $id";

        //ターゲットアカウント作成
        //アクセストークンが必要なため、tw_account_idを投げる
        $response = $this->post('/GetRequestToken');
        $response->assertStatus(200);
        
        // //ターゲットアカウントのフォロワーをゲット
        // var_dump("ターゲットアカウントのフォロワーをゲット");
        // $key_pattern_id = Tw_Account::find($id)->key_pattern_id;
        // var_dump("tw_account_id : $id");
        // var_dump("key_pattern_id : $key_pattern_id");
        // //アクセストークンが必要なため、tw_account_idを投げる
        // $response = $this->post('/createTargetFriends',
        //     [ 
        //         "key_pattern_id" => $key_pattern_id,
        //         "tw_account_id" => $id,
        //     ]
        // );
        // $response->assertStatus(200);


      

        $end_time = time();
        $time_passed = $end_time - $start_time;
        echo("     経過マイクロ秒: $time_passed");
    }

    public function getID_List() {
        
        $select_columns = implode([self::$model_info["pk"]]);
        $query = "?select_columns=".$select_columns;
        $id_list = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/RestApi'.''.$query)->__get("original");
        
        $who = rand(1, count($id_list)) - 1;
        $randam = $id_list[$who][self::$model_info["pk"]];
        return $randam;
    }
}
