<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwTargetFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tw__target__friends', function (Blueprint $table) {
            $table->bigIncrements('target_friend_id');
            $table->bigInteger('target_friend_user_id');
            
            $table->text('name');
            $table->text('screen_name');
            $table->bigInteger('followers_count');
            $table->bigInteger('friends_count');
            $table->text('description');
            $table->text('profile_image_url_https');

            $table->bigInteger('app_id');
            $table->bigInteger('target_account_id');
            $table->bigInteger('key_pattern_id')->nullable();
            $table->bigInteger('tw_account_id')->nullable();

            $table->dateTime('follow_at')->nullable();
            $table->dateTime('followed_at')->nullable();
            $table->dateTime('last_tw_at')->nullable();
            
            $table->boolean('blocked')->default(false);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw__target__friends');
    }
}
