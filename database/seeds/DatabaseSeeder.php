<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        
        $this->call([
            
            UserSeeder::class,//ok
            Key_PatternSeeder::class,//ok
            
            Tw_AccountSeeder::class,//ok
            
            Tw_TweetSeeder::class,//ok
            Tw_Auto_TweetSeeder::class,//ok

            Tw_Api_RequestSeeder::class,//ok
            Tw_Account_FriendSeeder::class,//ok
            Tw_Account_FollowSeeder::class,//ok

            Tw_Target_AccountSeeder::class,

            Tw_Target_FriendSeeder::class,
            // Tw_TargetSeeder::class,

        ]);
    }
}
