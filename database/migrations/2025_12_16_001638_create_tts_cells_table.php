<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTtsCellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tts_cells', function (Blueprint $table) {
    $table->id();
    $table->foreignId('puzzle_id')->constrained('tts_puzzles')->onDelete('cascade');
    $table->unsignedTinyInteger('x');
    $table->unsignedTinyInteger('y');
    $table->char('letter',1);
    $table->timestamps();

    $table->unique(['puzzle_id','x','y']);
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tts_cells');
    }
}
