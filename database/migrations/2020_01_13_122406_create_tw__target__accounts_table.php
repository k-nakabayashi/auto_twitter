<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwTargetAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tw__target__accounts', function (Blueprint $table) {
            $table->bigIncrements('target_account_id');
            $table->bigInteger('target_account_user_id');

            $table->text('name');
            $table->text('screen_name');
            $table->bigInteger('followers_count');
            $table->bigInteger('friends_count');
            $table->text('description');
            $table->text('profile_image_url_https');
         
            $table->bigInteger('app_id');
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
        Schema::dropIfExists('tw__target__accounts');
    }
}
