<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Trait_Model;
use Carbon\Carbon;

class ApiCounter extends Model
{
    use SoftDeletes, Trait_Model;
    protected $fillable = [
        'counting_started_at', 'counter',
        'request', 
        'tw_account_id',
        'max_daily_counter',
    ];
    protected $dates = ['deleted_at'];

    public $colums_for_create;
    public $colums_for_show;
    public $colums_for_edit;
    public $colums_for_destroy;

    public $request_list;

    //test用
    public $select_columns_for_show;
    public $select_columns_for_create;
    public $query_for_edit;
    public $select_columns_for_check_edtion;
    public $param;

    public function __construct()
    {   

        //１日の上限も設定できる
        $this->request_list = [
            'users_show' => 0,
            'followers_list' => 0,
            'friendships_create' => 400,
            'friendships_lookup' => 400,//一回で最大100件取得可能
            'friendships_destroy' => 1000,
            'search_tweets' => 0,
            'favorites_create' => 1000,
            'statuses_update' => 0,
        ];

        $this->no_duplication = ['request' => 'required', 'tw_account_id' => 'required'];
        $this->colums_for_create = ['request' => 'required', 'tw_account_id' => 'required'];
        $this->colums_for_show = ['request' => 'required', 'tw_account_id' => 'required'];
        $this->colums_for_edit = ['request' => 'required', 'tw_account_id' => 'required'];
        $this->colums_for_destroy = ['request' => 'required', 'tw_account_id' => 'required'];

    }
    public function getRequest_List () {
        return array_keys($this->request_list);
    }


}