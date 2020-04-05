<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Trait_Model;

/**
 * App\Tw_Target_Account
 *
 * @property int $target_account_id
 * @property int $target_account_user_id
 * @property string $detail
 * @property int $app_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tw_Target_Friend[] $tw_target_friends
 * @property-read int|null $tw_target_friends_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Target_Account onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account whereDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account whereTargetAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account whereTargetAccountUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tw_Target_Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Target_Account withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Tw_Target_Account withoutTrashed()
 * @mixin \Eloquent
 */
class Tw_Target_Account extends Model
{
    use SoftDeletes, Trait_Model;
    protected $primaryKey = 'target_account_id';
 
    protected $fillable = [
        'target_account_user_id', 

        'name', "screen_name", 
        "followers_count", "friends_count",
        "description", "profile_image_url_https",
        
        'app_id',
    ];

    private $no_duplication;

    private $colums_for_create;
    private $colums_for_show;
    private $colums_for_edit;
    private $colums_for_destroy;

    public function __construct()
    {

        $this->no_duplication = ['target_account_user_id' => 'required', 'app_id' => 'required'];
        $this->colums_for_create = ['target_account_user_id' => 'required', 'app_id' => 'required'];
        $this->colums_for_show = ['target_account_id' => 'required',];
        $this->colums_for_edit = ['target_account_id' => 'required',];
        $this->colums_for_destroy = ['target_account_id' => 'required',];

    }
}
