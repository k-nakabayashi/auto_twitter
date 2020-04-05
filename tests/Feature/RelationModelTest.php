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

//テスト
use Log;
use Mockery;
use Illuminate\Support\Str;


class RelationModelTest extends TestCase
{
    protected $action_class_name = "RestApi";

    protected static $test_model_list = array();
    protected static $model_info;

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

        self::$model_info = self::$test_model_list["App\Tw_Api_Request"];
        // self::$model_info = self::$test_model_list["App\Key_Pattern"];       
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1);
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

    public function testModels() {

        //親テーブル user
        $oya = app('App\User')->find(5); 
       
        //子テーブルのレコードをとってこれるか？ok
        echo "haMany   OK ";
        $ko = $oya->key_patterns()->first();
        // echo json_encode($ko->first());


        //親テーブルのレコードをとってこれるか？ ok 
        echo "belongsto";
        $ko = app('App\Key_Pattern')->where('app_id', 1)->first();
        $oya = $ko->User()->first();
        // var_dump ($oya->email);
    }
}
