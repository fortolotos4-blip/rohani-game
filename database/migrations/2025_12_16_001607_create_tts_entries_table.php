<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTtsEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tts_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('puzzle_id')->constrained('tts_puzzles')->onDelete('cascade');
    $table->foreignId('word_id')->constrained('tts_words')->onDelete('cascade');
    $table->enum('direction', ['across','down']);
    $table->unsignedTinyInteger('start_x');
    $table->unsignedTinyInteger('start_y');
    $table->unsignedTinyInteger('anchor_index')->nullable();
    $table->unsignedSmallInteger('number')->nullable();
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
        Schema::dropIfExists('tts_entries');
    }
}
