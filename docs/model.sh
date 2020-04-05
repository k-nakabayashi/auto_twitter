#!/bin/sh

php artisan make:model -m Tw_Api_Request
php artisan make:model -m Key_Pattern
php artisan make:model -m Tw_Account
php artisan make:model -m Tw_Account_Follow
php artisan make:model -m Tw_Account_Friend
php artisan make:model -m Tw_Auto_Favorite
php artisan make:model -m Tw_Favorite_Setting
php artisan make:model -m Tw_Auto_Tweet
php artisan make:model -m Tw_Target
php artisan make:model -m Tw_Target_Account
php artisan make:model -m Tw_Target_Friend
php artisan make:model -m Tw_Tweet


php artisan make:model -m ApiCounter;
php artisan make:model -m Favorite_Key_Pattern

