<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Tw_Target_Friend;
use App\Facades\ErrorService;
use Illuminate\Validation\Factory;
use Log;
use Carbon\Carbon;
class TargetFollowerController extends Controller
{

    public $req;
    public $app_id;
    public $tw_account_id;
    public $key_pattern_id;
    public $targets;
    public $factory;

    public function __construct(Request $req, Factory $factory)
    {
        Log::debug('22222');
        $this->req = $req;
        $this->app_id = Auth::id();
        $this->tw_account_id = $req->input("tw_account_id");
        $this->key_pattern_id = $req->input("key_pattern_id");

        $this->targets = Tw_Target_Friend::where([
            'tw_account_id' => $this->tw_account_id,
            'deleted_at' => null,
            'blocked' => false,
        ]);
        $this->factory =$factory;

        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     //フォロー済みリスト
    public function follow()
    {   
        Log::debug('follow');

        $model_list = $this->targets->where('follow_at', "!=", null)->get();
        return response()->json(['data' =>  $model_list, 'errors' => ErrorService::getMessage() ]);
    }

    
    public function unfollow()
    {   
        Log::debug('unfollow');

        $model_list = $this->targets->where('follow_at', null)->get();
        return response()->json(['data' =>  $model_list, 'errors' => ErrorService::getMessage() ]);

    }
    
    //フォロワーターゲットリスト
    public function follower_target()
    {   
        Log::debug('follower_target');

        $now = new Carbon();
        
        $model_list = $this->targets->where('follow_at', "!=", null)
        ->where('key_pattern_id', $this->key_pattern_id)
        ->whereDay('followed_at', ">=", $now->subDay(30))->get();

        return response()->json(['data' =>  $model_list, 'errors' => ErrorService::getMessage() ]);

    }

    public function follower()
    {   
        Log::debug('followers');

        $model_list = $this->targets->whereDay('followed_at', "!=", null)->get();
        return response()->json(['data' =>  $model_list, 'errors' => ErrorService::getMessage() ]);

    }
 
}
