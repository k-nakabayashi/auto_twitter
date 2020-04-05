<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Trait_Model;
use Log;
use App\ApiCounter;

class Tw_Account extends Model
{
    use SoftDeletes, Trait_Model;
    //
    protected $primaryKey = 'tw_account_id';

 
    protected $fillable = [
        'user_id', 
        'oauth_token', 'oauth_token_secret',
        'screen_name', 'app_id',
        'key_pattern_id',
        'favorite_key_pattern_id',

        
        //各種自動機能ラッパー判定 : 0:停止中, 1:起動中, 2:再開中, 3：一時停止中, 4:凍結中, 5:削除予約中
        //※２の再開中は、一時停止からの復帰、制限からの復帰を含みます。
        'follow', 'unfollow', 'tweet', 'favorite', 
        'follow_queue_id', 'unfollow_queue_id', 'favorite_queue_id', //'tweet_queue_id', 
    
        //※tweetは0,2,4のみ
        'tweet',
        
        // 0：停止中、1：起動中,  2：再開中、3：一時停止中
        //※凍結中でも、3の一時停止中になります。
        'auto_followers_list', 
        'auto_users_show',
        'auto_friendships_create',
        'auto_friendships_lookup',
        'auto_friendships_destroy',
        'auto_search_tweets',
        'auto_favorites_create',
        
        //0: 制限中、1:使用可能
        'followers_list',
        'users_show',
        'friendships_create',
        'friendships_lookup',
        'friendships_destroy',
        'search_tweets',
        'favorites_create',
        'statuses_update',

        
        'followers_list_counter',
        'users_show_counter',
        'friendships_create_counter',
        'friendships_lookup_counter',
        'friendships_destroy_counter',
        'search_tweets_counter',
        'favorites_create_counter',
        'statuses_update_create_counter',

        'suspended',

    ];

    protected $dates = ['deleted_at'];

    public $request_list;
    public $no_duplication;
    public $colums_for_create;
    public $colums_for_show;
    public $colums_for_edit;
    public $colums_for_destroy;



    public function __construct()
    {
        //
        $counter = new ApiCounter();
        $this->request_list = $counter->getRequest_List();       
        $this->no_duplication = ['user_id' => 'required',];
        $this->colums_for_create = ['user_id' => 'required', 'app_id' => 'required'];
        $this->colums_for_show = [];//['tw_account_id' => 'required'];
        $this->colums_for_edit = ['tw_account_id' => 'required',];
        $this->colums_for_destroy = ['tw_account_id' => 'required',];
    }

        
    public function getAutoFunc($str) {

        switch ($str) {
            case 'follow':
                return $this->follow;
                break;
            case 'unfollow':
                return $this->unfollow;
                break;
            case 'tweet':
                return $this->tweet;
                break;
            case 'favorite':
                return $this->favorite;
                break;
            case 'target_account':
                return $this->target_account;
                break;
            case 'my_account':
                return $this->my_account;
                break;
        }
    }
    
    public function setAutoFunc($str, $value) {

        switch ($str) {
            case 'follow':
                $this->follow = $value;
                break;
            case 'unfollow':
                $this->unfollow = $value;
                break;
            case 'tweet':
                $this->tweet = $value;
                break;
            case 'favorite':
                $this->favorite = $value;
                break;
            case 'target_account':
                $this->target_account = $value;
                break;
            case 'my_account':
                $this->my_account = $value;
                break;
        }
    }
    
    public function getApi_Reqeust($str) {

        switch ($str) {
            case $this->request_list[0]:
                return $this->users_show;
                break;
            case $this->request_list[1]:
                return $this->followers_list;
                break;
            case $this->request_list[2]:
                return $this->friendships_create;
                break;
            case $this->request_list[3]:
                return $this->friendships_lookup;
                break;
            case $this->request_list[4]:
                return $this->friendships_destroy;
                break;
            case $this->request_list[5]:
                return $this->search_tweets;
                break;
            case $this->request_list[6]:
                return $this->favorites_create;
                break;
            case $this->request_list[7]:
                return $this->statuses_update;
                break;
        }
    }

    public function setApi_Reqeust($str, $value) {
        
        switch ($str) {
            case $this->request_list[0]:
                $this->users_show = $value;
                break;
            case $this->request_list[1]:
                $this->followers_list = $value;
                break;
            case $this->request_list[2]:
                $this->friendships_create = $value;
                break;
            case $this->request_list[3]:
                $this->friendships_lookup = $value;
                break;
            case $this->request_list[4]:
                $this->friendships_destroy = $value;
                break;
            case $this->request_list[5]:
                $this->search_tweets = $value;
                break;
            case $this->request_list[6]:
                $this->favorites_create = $value;
                break;
            case $this->request_list[7]:
                $this->statuses_update = $value;
                break;
        }
    }
    
    
    public function getApi_Status($str) {
    
        switch ($str) {
            case $this->request_list[0]:
                return $this->auto_users_show;
                break;
            case $this->request_list[1]:
                return $this->auto_followers_list;
                break;
            case $this->request_list[2]:
                return $this->auto_friendships_create;
                break;
            case $this->request_list[3]:
                return $this->auto_friendships_lookup;
                break;
            case $this->request_list[4]:
                return $this->auto_friendships_destroy;
                break;
            case $this->request_list[5]:
                return $this->auto_search_tweets;
                break;
            case $this->request_list[6]:
                return $this->auto_favorites_create;
                break;
        }
    }

    public function setApi_Status($str, $value) {

        switch ($str) {
            case $this->request_list[0]:
                $this->auto_users_show = $value;
                break;
            case $this->request_list[1]:
                $this->auto_followers_list = $value;
                break;
            case $this->request_list[2]:
                $this->auto_friendships_create = $value;
                break;
            case $this->request_list[3]:
                $this->auto_friendships_lookup = $value;
                break;
            case $this->request_list[4]:
                $this->auto_friendships_destroy = $value;
                break;
            case $this->request_list[5]:
                $this->auto_search_tweets = $value;
                break;
            case $this->request_list[6]:
                $this->auto_favorites_create = $value;
                break;
        }
    }

    public function getApi_Counter($str) {

        switch ($str) {
            case $this->request_list[0]:
                return $this->users_show_counter;
                break;
            case $this->request_list[1]:
                return $this->followers_list_counter;
                break;
            case $this->request_list[2]:
                return $this->friendships_create_counter;
                break;
            case $this->request_list[3]:
                return $this->friendships_lookup_counter;
                break;
            case $this->request_list[4]:
                return $this->friendships_destroy_counter;
                break;
            case $this->request_list[5]:
                return $this->search_tweets_counter;
                break;
            case $this->request_list[6]:
                return $this->favorites_create_counter;
                break;
            case $this->request_list[7]:
                return $this->statuses_update_create_counter;
                break;
        }
    }

    public function setApi_Counter($str, $value) {

        switch ($str) {
            case $this->request_list[0]:
                $this->users_show_counter = $value;
                break;
            case $this->request_list[1]:
                $this->followers_list_counter = $value;
                break;
            case $this->request_list[2]:
                $this->friendships_create_counter = $value;
                break;
            case $this->request_list[3]:
                $this->friendships_lookup_counter = $value;
                break;
            case $this->request_list[4]:
                $this->friendships_destroy_counter = $value;
                break;
            case $this->request_list[5]:
                $this->search_tweets_counter = $value;
                break;
            case $this->request_list[6]:
                $this->favorites_create_counter = $value;
                break;
            case $this->request_list[7]:
                $this->statuses_update_create_counter = $value;
                break;

        }
    }

    public function getQueue_Id($str) {

        switch ($str) {
            case 'follow':
                return $this->follow_queue_id;
                break;
            case 'unfollow':
                return $this->unfollow_queue_id;
                break;
            case 'favorite':
                return $this->favorite_queue_id;
                break;
            case 'tweet':
                return $this->tweet_queue_id;
                break;
        }
    }

    public function setQueue_Id($str, $value) {

        switch ($str) {
            case 'follow':
                $this->follow_queue_id = $value;
                break;
            case 'unfollow':
                $this->unfollow_queue_id = $value;
                break;
            case 'favorite':
                $this->favorite_queue_id = $value;
                break;
            case 'tweet':
                $this->tweet_queue_id = $value;
                break;
        }
    }
    
    public function getPid($str) {

        switch ($str) {
            case 'follow':
                return $this->follow_pid;
                break;
            case 'unfollow':
                return $this->unfollow_pid;
                break;
            case 'favorite':
                return $this->favorite_pid;
                break;
        }
    }

    public function setPid($str, $value) {

        switch ($str) {
            case 'follow':
                $this->follow_pid = $value;
                break;
            case 'unfollow':
                $this->unfollow_pid = $value;
                break;
            case 'favorite':
                $this->favorite_pid = $value;
                break;
        }
    }



}
