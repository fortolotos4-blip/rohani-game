<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
        $table->string('image_path')->nullable();
        $table->text('prompt')->nullable(); // teks soal
        $table->string('answer_text')->nullable(); // jawaban benar (text)
        $table->text('explanation')->nullable(); // penjelasan/ayat
        $table->integer('time_limit_seconds')->default(16);
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
        Schema::dropIfExists('questions');
    }
}
