<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tw_Auto_TweetSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        //対象モデル
        $model_name =  preg_replace('#Seeder#', "", "App\\".__CLASS__);
        $model = app($model_name);
        
        //親モデル1
        $foreing_model_name1 = "App\Tw_Account";
        $foreing_model1 = app($foreing_model_name1);
        $count = $foreing_model1->all()->count();
        
        //親モデル2
        $foreing_model_name2 = "App\Tw_Tweet";
        $foreing_model2 = app($foreing_model_name2);

        
        $test = [];
        for ($i = 0; $i < 10; $i ++) {

            $picked_up_id1 = rand(1, ($count-1));

            $app_id = $foreing_model1->find($picked_up_id1)->getKey();
            $picked_up_id2 = $foreing_model2->find($app_id)->getKey();

            $radamStr = Str::random(10);
            // $radamInt =  $faker->randomFloat();//rand(1, 9999);
            $param = array(
                'tweet_status' =>  $radamStr,
                'tweet_timing' => $faker->dateTime(),
                'tw_account_id' => $picked_up_id1,
                'tw_tweet_id' => $picked_up_id2,
                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
            );
           
            for ($t = 0; $t < 3; $t ++) {
                DB::table($model->getTable())->insert($param);
            }
            
        }

    }
}
