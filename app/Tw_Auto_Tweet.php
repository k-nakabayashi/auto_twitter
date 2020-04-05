<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Trait_Model;
/**
 * App\Tw_Auto_Tweet
 *
 * @property int $id
 * @property string $tweet_status
 * @property string $tweet_timing
 * @property int $tw_account_id
 * @property int $tw_tweet_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User $tw_account
 * @property-read \App\Tw_Tweet $tw_tweet
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Auto_Tweet onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereTwAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereTwTweetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereTweetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereTweetTiming($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Auto_Tweet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Auto_Tweet withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Auto_Tweet withoutTrashed()
 * @mixin \Eloquent
 */
class Tw_Auto_Tweet extends Model
{
    use SoftDeletes, Trait_Model;
 
    protected $fillable = [

        // 0:停止中、1:予約中, 2:再開中 3:一時停止中, 4:posted, 5:削除予約中
        //制限statuses_updateはtw_accountで管理
        'tweet_status',

        //予約内容
        'detail', 
        'tags',
        'tweet_timing', 
        
        'tw_account_id', 
        'queue_id',
    ];

    private $no_duplication;

    private $colums_for_create;
    private $colums_for_show;
    private $colums_for_edit;
    private $colums_for_destroy;

    public function __construct()
    {
        $this->no_duplication = ['tw_account_id' => 'required', 'detail' => 'required'];
        $this->colums_for_create = ['detail' => 'required', 'tw_account_id' => 'required'];
        $this->colums_for_show = ['id' => 'required'];
        $this->colums_for_edit = ['id' => 'required'];
        $this->colums_for_destroy = ['id' => 'required'];
    }
}
