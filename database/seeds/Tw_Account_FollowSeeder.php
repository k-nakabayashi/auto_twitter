<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tw_Account_FollowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $model_name =  preg_replace('#Seeder#', "", "App\\".__CLASS__);
        $model = app($model_name);
        
        //親モデル
        $foreing_model_name = "App\Tw_Account";
        $foreing_model = app($foreing_model_name);
        
        $count = $foreing_model->all()->count();
                
        for ($i = 0; $i < 10; $i ++) {

            $picked_up_id = rand(1, ($count-1));

            $radamStr = Str::random(10);
            $radamInt =  $faker->randomNumber();
            $param = array(
                'friend_id' => $radamInt,
                'tw_account_id' => $picked_up_id,
                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
            );
           
            DB::table($model->getTable())->insert($param);
           
        }
    }
}
