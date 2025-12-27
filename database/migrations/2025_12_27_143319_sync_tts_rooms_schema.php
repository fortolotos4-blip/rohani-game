<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SyncTtsRoomsSchema extends Migration
{
    public function up()
    {
        Schema::table('tts_rooms', function (Blueprint $table) {

            if (!Schema::hasColumn('tts_rooms', 'player1_score')) {
                $table->integer('player1_score')->default(0);
            }

            if (!Schema::hasColumn('tts_rooms', 'player2_score')) {
                $table->integer('player2_score')->default(0);
            }

            if (!Schema::hasColumn('tts_rooms', 'player2')) {
                $table->string('player2')->nullable();
            }

            if (!Schema::hasColumn('tts_rooms', 'status')) {
                $table->string('status')->default('waiting');
            }
        });
    }

    public function down()
    {
        // biasanya dibiarkan kosong
    }
}
