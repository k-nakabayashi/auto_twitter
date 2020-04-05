#!/bin/sh
php artisan make:seeder Key_PatternSeeder;
php artisan make:seeder Tw_AccountSeeder;
php artisan make:seeder Tw_Account_FollowSeeder;
php artisan make:seeder Tw_Account_FriendSeeder;
php artisan make:seeder Tw_Api_RequestSeeder;
php artisan make:seeder Tw_Auto_FavoriteSeeder;
php artisan make:seeder Tw_Favorite_SettingSeeder;
php artisan make:seeder Tw_Auto_TweetSeeder;
php artisan make:seeder Tw_TargetSeeder;
php artisan make:seeder Tw_Target_AccountSeeder;
php artisan make:seeder Tw_Target_FriendSeeder;
php artisan make:seeder Tw_TweetSeeder;

php artisan make:factory ApiFactory -m Tw_Api_Request;
php artisan make:factory KeyPatternFactory -m Tw_Api_Request;
php artisan make:factory SnsAccountFactory -m Tw_Api_Request;
php artisan make:factory TargetAccountsFactory -m Tw_Api_Request;
php artisan make:factory TweetFactory -m Tw_Api_Request;
php artisan make:factory TweetTemplateFactory -m Tw_Api_Request;