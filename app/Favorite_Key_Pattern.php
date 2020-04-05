<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Trait_Model;

class Favorite_Key_Pattern extends Model
{
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

    //test用
    public $select_columns_for_show;
    public $select_columns_for_create;
    public $query_for_edit;
    public $select_columns_for_check_edtion;
    public $param;

    public function __construct()
    {
        $this->no_duplication = ['keyword' => 'required', 'app_id' => 'required'];
        $this->colums_for_create = ['keyword' => 'required', 'app_id' => 'required'];
        $this->colums_for_show = ['key_pattern_id' => 'required'];
        $this->colums_for_edit = ['key_pattern_id' => 'required',];
        $this->colums_for_destroy = ['key_pattern_id' => 'required',];

    }
}
