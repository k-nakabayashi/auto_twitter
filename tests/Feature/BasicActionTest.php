<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\User;
use Log;
use Mockery;

class ExampleTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(rand(1,9));
      
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

    public function testEndPoints(): void
    {

        $this->actingAs($this->user);
        // $result = $this->json('POST','/StartAutoFavoriteBySnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/StopAutFavoritBySnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/StartAutoFollowBySnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/StopAutoFollowBySnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/StartAutoUnfollowByAnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/StopAutoUnfollowBySnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/StartAutoTweetBySnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/StopAutoTweetBySnsAccount')->assertStatus(200);
        // $result = $this->json('POST','/ChooseKeyPatternForFavorite')->assertStatus(200);
        // $result = $this->json('POST','/ChooseKeyPatternForFollow')->assertStatus(200);
        // $result = $this->json('POST','/removeKeyPatternForFavorite')->assertStatus(200);
        // $result = $this->json('POST','/RemoveKeyPatternForFollow')->assertStatus(200);

        //TeitterAPIと連携確認
        $result = $this->json('POST', 
        '/CheckApi/getFriends_Ids',
        [
            '_token' => csrf_token(),
            'tw_account_id'=>1]
        );
        $result->assertStatus(200);

    }

    
    public function testLogout(): void
    {
        
        $this->actingAs($this->user);

        $response = $this->post(route('logout'), [
            '_token' => csrf_token(),
        ])->assertStatus(302);

        $this->assertGuest();
        
    }


}
