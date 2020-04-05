<?php

// use Carbon\Factory;
use Illuminate\Database\Seeder;
use App\Tw_Api_Request;

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class Tw_Api_RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Tw_Api_Request $model,Faker $faker)
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
                'api_request' => $radamStr,
                'count' => $faker->numberBetween(1, 16),
                'at_count_start' => $faker->dateTime(),
                'tw_account_id' => $picked_up_id,
                'created_at' => $faker->dateTime(),
                'updated_at' => $faker->dateTime(),
            );
           
            DB::table($model->getTable())->insert($param);
           
        }


    }
}
