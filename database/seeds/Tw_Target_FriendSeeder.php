<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tw_Target_FriendSeeder extends Seeder
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
        // $foreing_model_name = "App\Tw_Target_Account";
        // $foreing_model = app($foreing_model_name);

        $foreing_model_name = "App\User";
        $foreing_model = app($foreing_model_name);
        
        $count = $foreing_model->all()->count();
                
        for ($i = 0; $i < 1000; $i ++) {

            $picked_up_id = rand(1, ($count-1));
       

            $radanmStr = Str::random(10);
            $param = array(
                'target_friend_user_id' => 110114256,
                "name"=> "山口祐加＠自炊料理家",
                "screen_name"=> "yucca88",
                "followers_count" => 11951,
                "friends_count" => 485,
                "description" => "自炊する人を増やすために活動する料理家＆食のライター／ 日常の食を楽しく心地よくするために普段は #今日の一汁一菜 を作り、ハレの日は小さくて強い店を開拓します／料理初心者向けの自炊レッスンや週3で食材を使い切る #週3レシピ をnoteで連載中。好物はみそ汁。一汁一菜YoutTubeはじめました！",
                "profile_image_url_https" => "https://pbs.twimg.com/profile_images/1204269400783634433/F7-DwyL__normal.jpg",               
                'app_id' => 1,//$picked_up_id,
                'target_account_id' => 1,//$picked_up_id,
                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
                'follow_at' => $faker->dateTime(),
                'followed_at' => $faker->dateTime(),
                'last_tw_at' => $faker->dateTime(),
                'tw_account_id' => 1,
            );
            // for ($t = 0; $t < 3; $t ++) {
            DB::table($model->getTable())->insert($param);
            // }
        }

        // var_dump($test->guessBelongsToRelation());

    }
}
