<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwAutoTweetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tw__auto__tweets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('tweet_status')->default(0)->nullable(); //0:予約中 
            $table->dateTime('tweet_timing')->nullable();
            $table->bigInteger('tw_account_id')->require();
            $table->bigInteger('queue_id')->nullable();
            $table->text('detail')->nullable();
            $table->text('tags')->nullable();
            $table->bigInteger('pid')->default(0)->nullable();
            $table->softDeletes();
            $table->timestamps();

            // $table->bigInteger('tw_tweet_id')->require();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw__auto__tweets');
    }
}
