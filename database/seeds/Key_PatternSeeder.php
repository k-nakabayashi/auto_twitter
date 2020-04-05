<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Key_PatternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        //
        // Factory(Key_Pattern::class, 10)->create();
        $model_name =  preg_replace('#Seeder#', "", "App\\".__CLASS__);
        $model = app($model_name);
        
        //親モデル
        $foreing_model_name = "App\User";
        $foreing_model = app($foreing_model_name);
        
        $count = $foreing_model->all()->count();
               
        // for ($i = 0; $i < 3; $i ++) {
      
        // }
        $radanmStr = Str::random(10);
        $arr = [
            // ["txt" => "ああああああああ", "opt" => "and"],
            ["txt" => "料理", "opt" => "and"],
            ["txt" => "呑み旅人", "opt" => "or"],
            ["txt" => "日本酒", "opt" => "not"],
        ];
        $keyword  = json_encode($arr,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
        for ($i = 0; $i < 10; $i ++) {

            $picked_up_id = rand(1, ($count-1));

          
            $param = [];

            for ($t = 0; $t < 3; $t ++) {
                
                $param = [
                    'keyword' => $keyword,
                    'app_id' => $picked_up_id,
                    'created_at' => $faker->dateTime(),
                    'updated_at' => $faker->dateTime(),
                ];
                DB::table($model->getTable())->insert($param);
            }

        }
        // $keys = $model->all("keyword")->toArray();
        // foreach ($keys as $key) {
        //     $array = json_decode($key['keyword']);
        //     var_dump(($array));
        // }
        // var_dump($test->guessBelongsToRelation());

    }
}
