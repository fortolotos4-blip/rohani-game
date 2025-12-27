<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTtsAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tts_attempts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('puzzle_id');
    $table->string('player');
    $table->unsignedInteger('score');
    $table->unsignedSmallInteger('time_used');
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
        Schema::dropIfExists('tts_attempts');
    }
}
