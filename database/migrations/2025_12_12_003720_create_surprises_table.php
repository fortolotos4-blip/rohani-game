<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurprisesTable extends Migration
{
    public function up()
    {
        Schema::create('surprises', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // optional
            $table->text('verse');              // ayat/teks
            $table->text('action_text')->nullable(); // instruksi/praktik
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('surprises');
    }
}
