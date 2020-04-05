<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class Tw_Target_AccountSeeder extends Seeder
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
        $foreing_model_name = "App\Tw_Account";
        $foreing_model = app($foreing_model_name);
        
        $count = $foreing_model->all()->count();
                
        for ($i = 0; $i < 10; $i ++) {

            $picked_up_id = rand(1, ($count-1));

            $radanmStr = Str::random(10);
            $param = [
                "target_account_user_id" =>  110114256,
                "name"=> "山口祐加＠自炊料理家",
                "screen_name"=> "yucca88",
                "followers_count" => 11951,
                "friends_count" => 485,
                "description" => "自炊する人を増やすために活動する料理家＆食のライター／ 日常の食を楽しく心地よくするために普段は #今日の一汁一菜 を作り、ハレの日は小さくて強い店を開拓します／料理初心者向けの自炊レッスンや週3で食材を使い切る #週3レシピ をnoteで連載中。好物はみそ汁。一汁一菜YoutTubeはじめました！",
                "profile_image_url_https" => "http://pbs.twimg.com/profile_images/1204269400783634433/F7-DwyL__normal.jpg",
                'app_id' => $picked_up_id,
                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
            ];
            // for ($t = 0; $t < 3; $t ++) {
            DB::table($model->getTable())->insert($param);
            // }
        }

        // var_dump($test->guessBelongsToRelation());

    }
}
