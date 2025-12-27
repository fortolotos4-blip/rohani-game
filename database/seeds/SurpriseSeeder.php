<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SurpriseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'verse' => 'Mazmur 23:1 — TUHAN adalah gembalaku; takkan kekurangan aku.',
                'action_text' => 'Berdoalah syukur sejenak untuk berkat Tuhan hari ini.',
            ],
            [
                'verse' => 'Matius 5:16 — Biarlah terangmu bercahaya di depan orang, supaya mereka melihat perbuatanmu yang baik.',
                'action_text' => 'Lakukan satu kebaikan kecil hari ini.',
            ],
            [
                'verse' => 'Filipi 4:13 — Segala perkara dapat kutanggung di dalam Dia yang memberi kekuatan kepadaku.',
                'action_text' => 'Tuliskan satu hal yang Anda kuat lakukan karena Tuhan.',
            ],
            [
                'verse' => 'Amsal 3:5 — Percayalah kepada TUHAN dengan segenap hatimu.',
                'action_text' => 'Berbagi berkat: kirim pesan penyemangat ke satu teman.',
            ],
            [
                'verse' => 'Roma 12:10 — Hendaklah kamu saling mengasihi sebagai saudara dan saling mendahului dalam memberi hormat.',
                'action_text' => 'Lakukan satu tindakan pelayanan kecil hari ini.',
            ],
            [
                'verse' => '1 Yohanes 4:7 — Marilah kita saling mengasihi, karena kasih itu berasal dari Allah.',
                'action_text' => 'Luangkan waktu 5 menit untuk mendoakan orang lain.',
            ],
        ];

        foreach ($data as $row) {
            DB::table('surprises')->updateOrInsert(
                ['verse' => $row['verse']], // 🔑 kunci unik
                [
                    'action_text' => $row['action_text'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
