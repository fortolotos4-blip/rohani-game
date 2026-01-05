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
                'image_path'   => 'guess/ayub.jpg',
                'answer_text'  => 'AYUB',
                'answer_slots' => 4,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/babel.jpg',
                'answer_text'  => 'BABEL',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/daud.jpg',
                'answer_text'  => 'DAUD',
                'answer_slots' => 4,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/efod.jpg',
                'answer_text'  => 'EFOD',
                'answer_slots' => 4,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/esau.jpg',
                'answer_text'  => 'ESAU',
                'answer_slots' => 4,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/gideon.jpg',
                'answer_text'  => 'GIDEON',
                'answer_slots' => 6,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/habel.jpg',
                'answer_text'  => 'HABEL',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/hakimhakim.jpg',
                'answer_text'  => 'HAKIMHAKIM',
                'answer_slots' => 10,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/harun.jpg',
                'answer_text'  => 'HARUN',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/hosea.jpg',
                'answer_text'  => 'HOSEA',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/kain.jpg',
                'answer_text'  => 'KAIN',
                'answer_slots' => 4,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/kaleb.jpg',
                'answer_text'  => 'KALEB',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/kapernaum.jpg',
                'answer_text'  => 'KAPERNAUM',
                'answer_slots' => 9,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/keledai.jpg',
                'answer_text'  => 'KELEDAI',
                'answer_slots' => 7,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/korah.jpg',
                'answer_text'  => 'KORAH',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/mesir.jpg',
                'answer_text'  => 'MESIR',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/mordekhai.jpg',
                'answer_text'  => 'MORDEKHAI',
                'answer_slots' => 9,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/nehemia.jpg',
                'answer_text'  => 'NEHEMIA',
                'answer_slots' => 7,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/obaja.jpg',
                'answer_text'  => 'OBAJA',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/pengkotbah.jpg',
                'answer_text'  => 'PENGKOTBAH',
                'answer_slots' => 10,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/persia.jpg',
                'answer_text'  => 'PERSIA',
                'answer_slots' => 6,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/rajaraja.jpg',
                'answer_text'  => 'RAJARAJA',
                'answer_slots' => 8,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/rut.jpg',
                'answer_text'  => 'RUT',
                'answer_slots' => 3,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/samaria.jpg',
                'answer_text'  => 'SAMARIA',
                'answer_slots' => 7,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/samuel.jpg',
                'answer_text'  => 'SAMUEL',
                'answer_slots' => 6,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/ulangan.jpg',
                'answer_text'  => 'ULANGAN',
                'answer_slots' => 7,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/yakub.jpg',
                'answer_text'  => 'YAKUB',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/yeremia.jpg',
                'answer_text'  => 'YEREMIA',
                'answer_slots' => 7,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/yusuf.jpg',
                'answer_text'  => 'YUSUF',
                'answer_slots' => 5,
                'time_limit_seconds' => 60,
            ],
            [
                'image_path'   => 'guess/zefanya.jpg',
                'answer_text'  => 'ZEFANYA',
                'answer_slots' => 7,
                'time_limit_seconds' => 60,
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
