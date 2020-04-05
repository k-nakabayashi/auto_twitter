<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFavoriteKeyPatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favorite__key__patterns', function (Blueprint $table) {
            $table->bigIncrements('key_pattern_id');
            $table->bigInteger('app_id')->require();
            $table->text('keyword'); 
            // $table->json('keyword'); 
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
        Schema::dropIfExists('favorite__key__patterns');
    }
}
