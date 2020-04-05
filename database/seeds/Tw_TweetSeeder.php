<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tw_TweetSeeder extends Seeder
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
                
        for ($i = 0; $i < 10; $i ++) {

            $picked_up_id = rand(1, ($count-1));

            $radanmStr = Str::random(10);
            $param = array(
                'detail' => $radanmStr,
                'attachment' => $radanmStr,
                'app_id' => $picked_up_id,
                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
            );
            // for ($t = 0; $t < 3; $t ++) {
            DB::table($model->getTable())->insert($param);
            // }
        }

        // var_dump($test->guessBelongsToRelation());

    }
}
