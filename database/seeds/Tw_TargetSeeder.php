<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tw_TargetSeeder extends Seeder
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
        $foreing_model_name = "App\Tw_Target_Account";
        $foreing_model = app($foreing_model_name);

        //モデル
        $foreing_model_name2 = "App\Tw_Account";
        $foreing_model2 = app($foreing_model_name2);

        
        $count = $foreing_model->all()->count();
                
        for ($i = 0; $i < 10; $i ++) {

            $picked_up_id = rand(1, ($count-1));
       
            $record = $foreing_model2->select("key_pattern_id")->where("tw_account_id", $picked_up_id)->get()->toArray();
            if (isset($record[0]) === false) {
                continue;
            }

            $radanmStr = Str::random(10);
            $param = array(                
                'key_pattern_id' => $record[0]['key_pattern_id'],
                'target_friend_id' => $picked_up_id,
                'target_account_id' => $picked_up_id,
                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
            );
            // for ($t = 0; $t < 3; $t ++) {
            DB::table($model->getTable())->insert($param);
            // }
        }
    }
}
