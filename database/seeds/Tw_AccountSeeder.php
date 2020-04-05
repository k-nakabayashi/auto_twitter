<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tw_AccountSeeder extends Seeder
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

        //モデル2
        $foreing_model_name2 = "App\Key_Pattern";
        $foreing_model2 = app($foreing_model_name2);
        
        $count = $foreing_model->all()->count();
                
        for ($i = 0; $i < 10; $i ++) {

            $picked_up_id = rand(1, ($count-1));
            // $picked_up_id2 = "";//

            $records = $foreing_model2->select("key_pattern_id")->where('app_id', $picked_up_id)->get()->toArray();
            
            if (isset($records[0]) === false) {
                continue;
            }
  
            $radamStr = Str::random(10);
            $radamInt =  $faker->randomNumber();//rand(1, 9999);
            $param = array(
                'user_id' => 1,//env("TW_ID"),
                'oauth_token' => env("TW_ACCESS_KEY"),
                'oauth_token_secret' =>env("TW_ACCESS_SECRET"),
                'screen_name' => $radamStr,
                'name' => $radamStr,
                'description' => $radamStr,
                'app_id' => $picked_up_id,
                'key_pattern_id' =>  $records[0]['key_pattern_id'],
                'favorite_key_pattern_id' => $records[0]['key_pattern_id'],

                // 'auto_follow' => false,
                // 'auto_unfollow' => false,
                // 'auto_favorite' => false,
                // 'auto_tweet' => false,
                // 'auto_friendships_lookup' => false,

                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
            );
           
            DB::table($model->getTable())->insert($param);
           
        }

    }
}
