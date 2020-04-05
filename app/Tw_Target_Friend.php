<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Trait_Model;
/**
 * App\Tw_Target_Friend
 *
 * @property int $target_friend_id
 * @property int $target_friend_user_id
 * @property string $detail
 * @property int $target_account_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tw_Target[] $tw_target
 * @property-read int|null $tw_target_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Target_Friend onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend whereDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend whereTargetAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend whereTargetFriendId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend whereTargetFriendUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Friend whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Target_Friend withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Target_Friend withoutTrashed()
 * @mixin \Eloquent
 */
class Tw_Target_Friend extends Model
{
    use SoftDeletes, Trait_Model;


    protected $primaryKey = 'target_friend_id';
 
    protected $fillable = [
        'key_pattern_id', 'target_account_id',
        'app_id',
        'target_friend_user_id', 
        'tw_account_id',
        'follow_at', 'followed_at',
        
        'name','screen_name', 
        'followers_count','friends_count',
        'description','profile_image_url_https',
        'key_pattern_id',

        'follow_at', 'followed_at', 'last_tw_at',
        'blocked',
    ];

    private $no_duplication;

    private $colums_for_create;
    private $colums_for_show;
    private $colums_for_edit;
    private $colums_for_destroy;

    //test用
    public $select_columns_for_show;
    public $select_columns_for_create;
    public $query_for_edit;
    public $select_columns_for_check_edtion;
    public $param;

    public function __construct()
    {

        $this->no_duplication = [ 'target_friend_user_id' => 'required', 'target_account_id' => 'required',];
        $this->colums_for_create = [ 'target_friend_user_id' => 'required', 'target_account_id' => 'required', 'app_id' => 'required'];
        $this->colums_for_show = ['tw_account_id' => 'required'];
        $this->colums_for_edit = ['target_friend_id' => 'required', 'app_id' => 'required'];
        $this->colums_for_destroy = ['target_friend_id' => 'required',];

    }

    public function Tw_Target_Account () {
        return $this->belongsTo('App\Tw_Target_Account', 'target_account_id','target_account_id');   
    }

    // public function Key_pattern () {
    //     return $this->belongsTo('App\Key_Pattern', 'key_pattern_id','key_pattern_id');   
    // }

    //Scout 全文検索
    public function toSearchAbleArray()
    {
        return [
            'name' => $this->name,
            'email'      => $this->email,
        ];
    }
    //通常検索用カラム
    public function getSearchColumns()
    {
        return array_keys($this->toSearchAbleArray());
    }

}
