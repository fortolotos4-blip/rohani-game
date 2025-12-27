<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ForceMakePuzzleIdNullablePgsql extends Migration
{
    public function up()
    {
        // PostgreSQL-safe: drop NOT NULL constraint
        DB::statement('ALTER TABLE tts_rooms ALTER COLUMN puzzle_id DROP NOT NULL');
    }

    public function down()
    {
        // optional: jangan dikembalikan
    }
}
