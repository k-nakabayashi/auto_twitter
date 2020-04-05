<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
//通信

//DB;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Tw_Target_Friend;
use App\Tw_Account;
use Illuminate\Support\Facades\DB;

//job
use App\Jobs\TargetFriendRoopJob;
//テストS
use Mockery;

class TargetFriendRoopJobTest extends TestCase
{

    protected $action_class_name = "RestApi";
    protected $user;

    public static function setUpBeforeClass()
    {
 
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
    public function testEndpoint()
    {        
        $this->actingAs($this->user);

        
        $url = "/startAutoFunction";
        $data = ["tw_account_id"=>2,"domain"=>"unfollow","pattern"=>"start"];
        //action => index
        $result = $this->withHeaders($data)
        ->json('POST', $url, [
        ])->assertStatus(200);        
    }

    public function testFriendshipRoopJob()
    {        
        $this->actingAs($this->user);
        $id = 1;//tw_account_id
        $my_tw_account = Tw_Account::find($id);

                //2.フォローしたユーザを100件ずつ取得
        $model = new Tw_Target_Friend;

        $data = [
            'target_friend_id',
            'target_friend_user_id',
            'follow_at', 'followed_at',
        ];

        $tw_target_account_list = DB::table($model->getTable())
        ->paginate(100, $data, 'page', 1);

        //フレンドシップ取得対象
        $data_list = [];
        foreach ($tw_target_account_list->items() as $item){
            array_push($data_list,$item->target_friend_user_id);
        }


        //APIリクエストの設定
        $request_method = 'GET' ;
        $params = array(
            "user_id" => $data_list,
        );
        $request_url = 'https://api.twitter.com/1.1/friendships/lookup.json';

        //次のページを取得
        $next = $tw_target_account_list->nextPageUrl();
        if ($next != null) {
            //nullなら次のページなし
            $next = (int) substr( $next, -1 );
        }
            //onQueueの名前
        //まだ起動未確認

        //3.Jobの設定
        // $my_tw_account->auto_friendships_lookup = 1;//「起動中」という判定になる
        $my_tw_account->save();

        $job_dto = [

          "tw_target_account_list" => $tw_target_account_list->items(),
          "request_method"=>$request_method, 
          "request_url"=>$request_url, 
          "params"=>$params, 
          "my_tw_account"=>$my_tw_account,
          'next' => $next, 
        

          //リクエストとQueue管理用
          "api_requst" => 'friendships_lookup',
          'api_requst2' => 'users_show', 
          "queue_name" => "friendships_lookup",
          "queue_name2" => "friendships_lookup",
          'queue_id' => 0,
          'queue_id2' => 0,
        ];

        Bus::fake();
        Bus::assertNotDispatched(TargetFriendRoopJob::class);
        TargetFriendRoopJob::dispatch($job_dto, 0)
        ->delay(now()->addMinutes(config('api.RESTART_QUEUE')));
        Bus::assertDispatched(TargetFriendRoopJob::class);
        

            
    }
}
