<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePuzzleIdNullableOnTtsRooms extends Migration
{
    public function up()
    {
        Schema::table('tts_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('puzzle_id')->nullable()->change();
        });
    }

    public function down()
    {
        // tidak perlu rollback
    }
}
