<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('attempts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('question_id')->constrained()->onDelete('cascade');
        $table->unsignedBigInteger('user_id')->nullable(); // optional
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        $table->foreignId('choice_id')->nullable()->constrained('choices')->onDelete('set null');
        $table->boolean('correct')->default(false);
        $table->integer('time_taken_seconds')->nullable();
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
        Schema::dropIfExists('attempts');
    }
}
