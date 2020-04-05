<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_counters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('counting_started_at')->nullable();
            $table->bigInteger('counter')->default(0)->nullable();
            $table->bigInteger('max_daily_counter')->default(0)->nullable();

            $table->string('request');

            $table->bigInteger('tw_account_id');
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
        Schema::dropIfExists('api_counters');
    }
}
