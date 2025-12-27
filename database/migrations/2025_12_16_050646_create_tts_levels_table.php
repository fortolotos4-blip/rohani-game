<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTtsLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tts_levels', function(Blueprint $t){
    $t->id();
    $t->string('name');
    $t->unsignedTinyInteger('grid_size');
    $t->unsignedTinyInteger('word_count');
    $t->unsignedSmallInteger('time_limit');
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tts_levels');
    }
}
