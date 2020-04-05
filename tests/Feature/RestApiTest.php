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

class RestApiTest extends TestCase
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

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testShowAll()
    {        
        $this->actingAs($this->user);

        //3 全　データを取れるか？
        //action => index
        $result = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name, [
        ])->assertStatus(200);

    }


    public function testShow()
    {
        ///////index select_columnsの有無確認
        $select_columns = [];
        $result = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name, $select_columns)->assertStatus(200);
        
        $select_columns = ["select_columns"=> self::$model_info["select_columns_for_show"]];
        $result = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name, $select_columns)->assertStatus(200);
    }

    public function testCreate()
    {   
        $this->actingAs($this->user);

        //insert
        //action => create, show
        $result = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/create', self::$model_info["param"]);
        $result->assertStatus(200);
        

        ////////show 確認する
        $select_columns = ["select_columns" => self::$model_info["select_columns_for_create"] ];
        $query = "?api_request=".self::$model_info["param"]['api_request']."&tw_account_id=".self::$model_info["param"]['tw_account_id'];

        $show_data = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/show'.$query, $select_columns);
        $show_data->assertStatus(200);

        //3 show  select_columnsの有無テスト
        $show_data = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/show'.$query)->assertStatus(200);
        
        $select_columns = [];
        $show_data = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/show'.$query, $select_columns)->assertStatus(200);
    
    }

    public function testEdit()
    {
        $this->actingAs($this->user);
        //////idを選択し次のテストで使う。
        $randam  = $this->getID_List();
        // echo "更新対象ID：".$randam;

        //更新したのち更新通りになっているか？
        //action => edit, show
        $query = "?".self::$model_info["pk"]."=".$randam."&".self::$model_info["query_for_edit"];
        
        $result = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/edit/edit'.$query)->assertStatus(200);
        
        $show_Data = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/show'.$query)->assertStatus(200);//destoryテストで使うためここではassertしない
    
        ////select_columnsの有無確認　更新後のデータの取り方
        $select_columns = [];
        $result = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/edit/edit'.$query, $select_columns)->assertStatus(200);
        
        
        $select_columns = ["select_columns" => self::$model_info["select_columns_for_check_edtion"]];
        $result = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.'/edit/edit'.$query, $select_columns)->assertStatus(200);
    }

    public function testDestory()
    {
        $this->actingAs($this->user);

        //////idを選択し次のテストで使う。
        $randam  = $this->getID_List();
        // echo "削除対象ID：".$randam."\r";
 
        //データ削除はできるか？
        //action =>destory,
        $target = app(self::$model_info["model_name"])->find($randam)->attributesToArray();
        $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->assertDatabaseHas(self::$model_info["table_name"], $target); 

        $query = "?".self::$model_info["pk"]."=".$randam;
        $result = $this->json('DELETE', '/'.$this->action_class_name.'/delete'.$query)->assertStatus(200);
        $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->assertDatabaseMissing(self::$model_info["table_name"], $target); 
    
    }

    public function getID_List() {
        $select_columns = implode([self::$model_info["pk"]]);
        $query = "?select_columns=".$select_columns;
        $id_list = $this->withHeaders(["model_name" => self::$model_info["model_name"]])
        ->json('GET', '/'.$this->action_class_name.''.$query)->__get("original");
        $who = rand(1, count($id_list)) - 1;
        $randam = $id_list[$who][self::$model_info["pk"]];
        return $randam;
    }
}
