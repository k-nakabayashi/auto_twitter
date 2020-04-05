#!/bin/sh

php artisan make:test RestApiTest;
php artisan make:test RestKeyPatternTest;
php artisan make:test RestSnsAccountTest;
php artisan make:test RestTargetAccountsTest;
php artisan make:test RestTweetTest;
php artisan make:test RestTweetTemplateTest;

php artisan db:seed --class=Tw_Target_FriendSeeder
phpunit ./tests/Feature/FriendShipsLookupTest.php