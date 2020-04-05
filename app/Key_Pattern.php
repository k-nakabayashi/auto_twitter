<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Trait_Model;

/**
 * App\Key_Pattern
 *
 * @property int $key_pattern_id
 * @property int $app_id
 * @property string $keyword
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tw_Favorite_Setting[] $tw_favorite_settings
 * @property-read int|null $tw_favorite_settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tw_Target[] $tw_targets
 * @property-read int|null $tw_targets_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Key_Pattern onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern whereKeyPatternId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Key_Pattern whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Key_Pattern withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Key_Pattern withoutTrashed()
 * @mixin \Eloquent
 */
class Key_Pattern extends Model
{
    //
    use SoftDeletes, Trait_Model;

    protected $primaryKey = 'key_pattern_id';
    protected $fillable = [
        'keyword', 
        'app_id',
    ];
    // protected $casts = [
    //     'keyword' => 'json',  // ココ
    // ];

    private $no_duplication;
    private $colums_for_create;
    private $colums_for_show;
    private $colums_for_edit;
    private $colums_for_destroy;



    public function __construct()
    {

        $this->no_duplication = ['keyword' => 'required', 'app_id' => 'required'];
        $this->colums_for_create = ['keyword' => 'required', 'app_id' => 'required'];
        $this->colums_for_show = ['key_pattern_id' => 'required'];
        $this->colums_for_edit = ['key_pattern_id' => 'required',];
        $this->colums_for_destroy = ['key_pattern_id' => 'required',];

    }

}
