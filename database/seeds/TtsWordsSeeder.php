<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TtsWordsSeeder extends Seeder
{
    public function run()
    {
        $words = [
            ['YESUS', 'Tokoh utama dalam Injil', 'tokoh', 'easy'],
            ['DAUD', 'Raja Israel yang mengalahkan Goliat', 'tokoh', 'easy'],
            ['MUSA', 'Pemimpin Israel keluar dari Mesir', 'tokoh', 'easy'],
            ['RAJA', 'Pemimpin kerajaan', 'konsep', 'easy'],
            ['NABI', 'Utusan Tuhan', 'konsep', 'easy'],
            ['MARTA', 'Saudara perempuan Maria dari Betania', 'tokoh', 'easy'],
            ['WAHYU', 'Kitab PB tentang akhir zaman', 'konsep', 'easy'],
            ['HENOKH', 'Tokoh PL yang diangkat Tuhan', 'tokoh', 'easy'],
            ['OBAJA', 'Kitab tentang nubuat atas Edom', 'konsep', 'easy'],
            ['DOA', 'Berkomunikasi dengan Tuhan', 'konsep', 'easy'],
            ['KANAAN', 'Tanah perjanjian Israel', 'konsep', 'easy'],
            ['IMAN', 'Kepercayaan kepada Tuhan', 'konsep', 'easy'],

            ['YOHANES', 'Yang membaptis Yesus', 'tokoh', 'medium'],
            ['NAAMAN', 'Panglima Aram yang disembuhkan', 'tokoh', 'medium'],
            ['HIZKIA', 'Raja yang diperpanjang umurnya', 'tokoh', 'medium'],
            ['DEBORA', 'Hakim dan pemimpin wanita Israel', 'tokoh', 'medium'],
            ['KASIH', 'Hukum terutama dalam PB', 'konsep', 'medium'],
            ['LEWI', 'Suku pelayanan Tuhan', 'konsep', 'medium'],
            ['YOSUA', 'Penerus Musa', 'tokoh', 'medium'],
            ['KALEB', 'Tokoh yang masuk tanah perjanjian', 'tokoh', 'medium'],

            ['NAZARET', 'Kota asal Yesus', 'konsep', 'hard'],
            ['NABOT', 'Pemilik kebun anggur yang dirampas Ahab', 'tokoh', 'hard'],
            ['ESTER', 'Ratu Persia dari bangsa Yahudi', 'tokoh', 'hard'],
            ['KERIT', 'Sungai tempat Elia dipelihara', 'konsep', 'hard'],
            ['ROH', 'Buah Roh membentuk karakter', 'konsep', 'hard'],
            ['HAGAI', 'Nabi pembangunan Bait Allah', 'tokoh', 'hard'],
        ];

        foreach ($words as [$word, $clue, $category, $difficulty]) {

            DB::table('tts_words')->updateOrInsert(
                ['word' => strtoupper($word)], // 🔒 KUNCI UNIK
                [
                    'clue' => $clue,
                    'length' => mb_strlen($word),
                    'category' => $category,
                    'difficulty' => $difficulty,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
