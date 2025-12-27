<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TtsWordsSeeder extends Seeder
{
    public function run()
    {
        $words = [
            [
                'word' => 'YESUS',
                'clue' => 'Tokoh utama dalam Injil',
                'category' => 'tokoh',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'DAUD',
                'clue' => 'Raja Israel yang mengalahkan Goliat',
                'category' => 'tokoh',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'MUSA',
                'clue' => 'Pemimpin Israel keluar dari Mesir',
                'category' => 'tokoh',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'RAJA',
                'clue' => 'Pemimpin kerajaan',
                'category' => 'konsep',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'NABI',
                'clue' => 'Utusan Tuhan',
                'category' => 'konsep',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'MARTA',
                'clue' => 'Saudara Perempuan Maria dari Betania',
                'category' => 'tokoh',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'WAHYU',
                'clue' => 'Alkitab Perjanjian Baru yang menceritakan Akhir Jaman',
                'category' => 'konsep',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'HENOKH',
                'clue' => 'Seorang tokoh perjanjian lama yang diangkat Tuhan karena hidupnya berkenan kepada Allah',
                'category' => 'tokoh',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'OBAJA',
                'clue' => 'Menceritakan nubuat Allah tentang penghakiman atas bangsa Edom',
                'category' => 'konsep',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'DOA',
                'clue' => 'Berkomunikasi dengan Tuhan',
                'category' => 'konsep',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'KANAAN',
                'clue' => 'Tanah perjanjian yang diberikan kepada bangsa Israel',
                'category' => 'konsep',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'IMAN',
                'clue' => 'Kepercayaan dalam diri seseorang terhadap Tuhan',
                'category' => 'konsep',
                'difficulty' => 'easy',
            ],
            [
                'word' => 'YOHANES',
                'clue' => 'Yang membaptis Yesus di sungai yordan',
                'category' => 'tokoh',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'NAAMAN',
                'clue' => 'Panglima raja aram yang disembuhkan kusta nya',
                'category' => 'tokoh',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'HIZKIA',
                'clue' => 'Seorang tokoh yang diperpanjang umurnya 15 tahun oleh Tuhan dari permohonan doa',
                'category' => 'tokoh',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'DEBORA',
                'clue' => 'Seorang tokoh yang diceritakan di perjanjian lama sebagai Hakim dan pemimpin militer wanita',
                'category' => 'tokoh',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'KASIH',
                'clue' => 'Hukum yang terutama tertulis di alkitab perjanjian baru (Matius)',
                'category' => 'konsep',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'LEWI',
                'clue' => 'Suku yang dipilih Tuhan untuk mengurus perbendaharaan dan pelayanan Tuhan',
                'category' => 'konsep',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'YOSUA',
                'clue' => 'Seorang tokoh yang menggantikan musa saat membawa bangsa israel menuju tanah perjanjian',
                'category' => 'tokoh',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'KALEB',
                'clue' => 'Seorang tokoh yang di izinkan masuk ke tanah perjanjian',
                'category' => 'tokoh',
                'difficulty' => 'medium',
            ],
            [
                'word' => 'NAZARET',
                'clue' => 'Sebuah kota kecil sebagai tempat asal dan tinggal Yesus saat berada di bumi',
                'category' => 'tokoh',
                'difficulty' => 'hard',
            ],
            [
                'word' => 'NABOT',
                'clue' => 'Seorang warga Yizreel mempunyai kebun anggur yang di ingini oleh raja Ahab',
                'category' => 'tokoh',
                'difficulty' => 'hard',
            ],
            [
                'word' => 'ESTER',
                'clue' => 'Perempuan Yahudi yang diangkat menjadi Ratu oleh raja Ahasuerus',
                'category' => 'tokoh',
                'difficulty' => 'hard',
            ],
            [
                'word' => 'KERIT',
                'clue' => 'Sungai yang ditunjukan Tuhan kepada Elia untuk memelihara hidupnya',
                'category' => 'konsep',
                'difficulty' => 'hard',
            ],
            [
                'word' => 'ROH',
                'clue' => 'Buah yang diajarkan Tuhan untuk membentuk karakter Allah pada seseorang',
                'category' => 'konsep',
                'difficulty' => 'hard',
            ],
            [
                'word' => 'HAGAI',
                'clue' => 'Seorang nabi yang diutus untuk membangun kembali Bait Suci Tuhan di Yerusalem',
                'category' => 'tokoh',
                'difficulty' => 'hard',
            ],
            // 🔥 TAMBAH TERUS DI SINI
        ];

        foreach ($words as $w) {
            DB::table('tts_words')->insert([
                'word' => strtoupper($w['word']),
                'clue' => $w['clue'],
                'length' => strlen($w['word']),
                'category' => $w['category'],
                'difficulty' => $w['difficulty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
