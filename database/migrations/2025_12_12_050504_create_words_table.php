<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWordsTable extends Migration
{
    public function up()
    {
        Schema::create('words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('word');           // kata (tanpa spasi idealnya)
            $table->string('clue_across')->nullable();
            $table->string('clue_down')->nullable();
            $table->tinyInteger('difficulty')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('words');
    }
}
