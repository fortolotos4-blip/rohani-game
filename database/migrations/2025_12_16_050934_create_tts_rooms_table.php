<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTtsRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('tts_rooms', function (Blueprint $table) {
            $table->id();

            $table->string('room_code')->unique();

            $table->foreignId('puzzle_id')
                  ->constrained('tts_puzzles')
                  ->onDelete('cascade');

            // bisa pakai user_id kalau pakai auth
            $table->string('player1');
            $table->string('player2')->nullable();

            $table->string('current_turn')->nullable();

            $table->enum('status', ['waiting','playing','finished'])
                  ->default('waiting');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tts_rooms');
    }
}
