<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTtsPuzzlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tts_puzzles', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->unsignedTinyInteger('grid_size')->default(10);
        $table->enum('status', ['draft','active'])->default('draft');
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
        Schema::dropIfExists('tts_puzzles');
    }
}
