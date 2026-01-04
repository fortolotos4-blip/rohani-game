<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Question;

class GuessImageSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'image_path'   => 'guess/babel.png',
                'answer_text'  => 'BABEL',
                'answer_slots' => 5,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/bilangan.png',
                'answer_text'  => 'BILANGAN',
                'answer_slots' => 8,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/gideon.png',
                'answer_text'  => 'GIDEON',
                'answer_slots' => 6,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/harun.png',
                'answer_text'  => 'HARUN',
                'answer_slots' => 5,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/kapernaum.png',
                'answer_text'  => 'KAPERNAUM',
                'answer_slots' => 9,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/mesir.png',
                'answer_text'  => 'MESIR',
                'answer_slots' => 5,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/pengkotbah.png',
                'answer_text'  => 'PENGKOTBAH',
                'answer_slots' => 10,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/persia.png',
                'answer_text'  => 'PERSIA',
                'answer_slots' => 6,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/samaria.png',
                'answer_text'  => 'SAMARIA',
                'answer_slots' => 7,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/zefanya.png',
                'answer_text'  => 'ZEFANYA',
                'answer_slots' => 7,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/ayub.png',
                'answer_text'  => 'AYUB',
                'answer_slots' => 4,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/hakim.png',
                'answer_text'  => 'HAKIMHAKIM',
                'answer_slots' => 10,
                'time_limit_seconds' => 30,
            ],
            [
                'image_path'   => 'guess/kandil.png',
                'answer_text'  => 'KANDIL',
                'answer_slots' => 6,
                'time_limit_seconds' => 30,
            ],
        ];

        foreach ($data as $row) {
            Question::updateOrCreate(
                [
                    'image_path' => $row['image_path'], // UNIQUE KEY
                ],
                $row
            );
        }
    }
}
