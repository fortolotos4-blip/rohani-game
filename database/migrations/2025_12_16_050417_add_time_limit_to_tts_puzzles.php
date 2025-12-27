<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeLimitToTtsPuzzles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tts_puzzles', function (Blueprint $table) {
    $table->unsignedSmallInteger('time_limit')->default(300);
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tts_puzzles', function (Blueprint $table) {
            //
        });
    }
}
