<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tw__accounts', function (Blueprint $table) {
            $table->bigIncrements('tw_account_id');
            $table->bigInteger('app_id');
            $table->bigInteger('user_id');
            $table->bigInteger('key_pattern_id')->nullable();
            $table->bigInteger('favorite_key_pattern_id')->nullable();
            $table->string('screen_name')->nullable();
            $table->text('oauth_token')->nullable();
            $table->text('oauth_token_secret')->nullable();
            $table->text('profile_image_url_https')->nullable();
            $table->text('name')->nullable();
            $table->text('description')->nullable();
            
            //api状態 : 停止中、起動中、再開待ち
            //制限くらう→停止・再開待ち
            //動的に停止→停止only,別で再発行jobが必要

            //各種自動機能 判定 : 0:停止中, 1:起動中, 2:再開中, 3:制限中
            $table->Integer('follow')->default(0);
            $table->Integer('unfollow')->default(0);
            $table->Integer('tweet')->default(0);
            $table->Integer('favorite')->default(0);

            //fail→再開用：queue_idを保持していく
            $table->bigInteger('follow_queue_id')->default(0)->nullable();
            $table->bigInteger('unfollow_queue_id')->default(0)->nullable();
            $table->bigInteger('favorite_queue_id')->default(0)->nullable();

            //api制限中は「起動中」になる
            //停止するなら、
            //各種APIリクエストJOB　起動判定 : 0:停止中, 1:起動中, 2:再開中 3：一時停止中
            $table->Integer('auto_followers_list')->default(0);
            $table->Integer('auto_users_show')->default(0);
            $table->Integer('auto_friendships_create')->default(0);
            $table->Integer('auto_friendships_lookup')->default(0);
            $table->Integer('auto_friendships_destroy')->default(0);
            $table->Integer('auto_search_tweets')->default(0);
            $table->Integer('auto_favorites_create')->default(0);


            //api上限判定 : trueの場合使える
            $table->boolean('followers_list')->default(true);
            $table->boolean('users_show')->default(true);
            $table->boolean('friendships_create')->default(true);
            $table->boolean('friendships_lookup')->default(true);
            $table->boolean('friendships_destroy')->default(true);
            $table->boolean('search_tweets')->default(true);
            $table->boolean('favorites_create')->default(true);
            $table->boolean('statuses_update')->default(true);

            //API_COUNTERとの紐つけ
            $table->bigInteger('followers_list_counter')->nullable();
            $table->bigInteger('users_show_counter')->nullable();
            $table->bigInteger('friendships_create_counter')->nullable();
            $table->bigInteger('friendships_lookup_counter')->nullable();
            $table->bigInteger('friendships_destroy_counter')->nullable();
            $table->bigInteger('search_tweets_counter')->nullable();
            $table->bigInteger('favorites_create_counter')->nullable();
            $table->bigInteger('statuses_update_create_counter')->nullable();

            //凍結判定
            $table->boolean('suspended')->default(false);

            // $table->boolean('auto_tweet')->default(false);
            $table->string('queue_name')->nullable();

            //フォロー5000件を超えたら、アンフォロー機能が使える、というための判定Flag
            $table->boolean('unfollow_flag')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw__accounts');
    }
}
