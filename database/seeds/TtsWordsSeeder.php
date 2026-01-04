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
            ['ABRAHAM', 'Bapa orang beriman', 'tokoh', 'easy'],
            ['NATAL', 'Peringatan Hari Kelahiran Yesus', 'konsep', 'easy'],
            ['TIGA', 'Hari keberapa Tuhan Yesus bangkit ?', 'konsep', 'easy'],
            ['ESAU', 'Tokoh PL yang menjual hak kesulungan nya', 'tokoh', 'easy'],
            ['BINTANG', 'Orang Majus dulu bisa sampai ke Yerusalem dengan mengikuti ?', 'konsep', 'easy'],
            ['KELEDAI', 'Ketika ada di yerusalem, Yesus menyuruh muridnya membawakan seekor ?', 'konsep', 'easy'],
            ['HABEL', 'Anak perempuan dari Adam dan Hawa yang mengembalakan domba', 'tokoh', 'easy'],
            ['MANA', 'Makanan yang diberikan Tuhan saat bangsa israel ada di padang gurun', 'konsep', 'easy'],
            ['RUBEN', 'Adik kandung Yusuf PL', 'tokoh', 'easy'],
            ['GEREJA', 'Tempat berkumpul untuk ibadah di hari minggu', 'konsep', 'easy'],
            ['RAHEL', 'Yakub menunggu wanita yang dicintainya selama bertahun-tahun', 'tokoh', 'easy'],
            ['LOT', 'Seseorang yang diselamatkan dari Sodom dan Gomora tetapi tidak pada istrinya', 'tokoh', 'easy'],
            ['PALUNGAN','Tempat Yesus dibaringkan saat lahir', 'konsep', 'easy'],

            ['YOHANES', 'Yang membaptis Yesus', 'tokoh', 'medium'],
            ['NAAMAN', 'Panglima Aram yang disembuhkan', 'tokoh', 'medium'],
            ['HIZKIA', 'Raja yang diperpanjang umurnya', 'tokoh', 'medium'],
            ['DEBORA', 'Hakim dan pemimpin wanita Israel', 'tokoh', 'medium'],
            ['KASIH', 'Hukum terutama dalam PB', 'konsep', 'medium'],
            ['LEWI', 'Suku pelayanan Tuhan', 'konsep', 'medium'],
            ['YOSUA', 'Penerus Musa', 'tokoh', 'medium'],
            ['KALEB', 'Tokoh yang masuk tanah perjanjian', 'tokoh', 'medium'],
            ['NIL', 'Sungai dimana Musa diambil oleh putri Firaun', 'konsep', 'medium'],
            ['MESIR', 'Tokoh Yusuf PL dijual oleh saudaranya dan dibawa ke ?', 'konsep', 'medium'],
            ['KORAH', 'Seorang suku lewi yang ditelan bumi karena kesombongan', 'tokoh', 'medium'],
            ['NAOMI', 'Ibu mahlon', 'tokoh', 'medium'],
            ['UZA', 'Seorang suku lewi yang dimatikan Tuhan karena kecerobohan dalam membawa Kemah suci', 'tokoh', 'medium'],
            ['TUJUH', 'Pada hari keberapa Tuhan Yesus berhenti mengerjakan penciptaan', 'konsep', 'medium'],
            ['AKHAN', 'Sesorang yang mencuri barang-barang dikhususkan bagi Tuhan dari Yerikho', 'tokoh', 'medium'],
            ['AI', 'Tempat dimana bangsa israel kalah perang karena dosa seseorang yang mencuri brang kudus Tuhan', 'konsep', 'medium'],
            ['LAZARUS', 'Yesus membangkitkan saudara Marta dan Maria yang meninggal', 'tokoh', 'medium'],
            ['MATA', 'Ketika kita berdoa, kita harus menutup', 'konsep','medium'],
            ['TALENTA', 'karunia, kemampuan, atau kepercayaan yang Tuhan berikan kepada setiap orang', 'konsep', 'medium'],

            ['NAZARET', 'Kota asal Yesus', 'konsep', 'hard'],
            ['NABOT', 'Pemilik kebun anggur yang dirampas Ahab', 'tokoh', 'hard'],
            ['ESTER', 'Ratu Persia dari bangsa Yahudi', 'tokoh', 'hard'],
            ['KERIT', 'Sungai tempat Elia dipelihara', 'konsep', 'hard'],
            ['ROH', 'Buah Roh membentuk karakter', 'konsep', 'hard'],
            ['HAGAI', 'Nabi pembangunan Bait Allah', 'tokoh', 'hard'],
            ['PUASA', 'Hal yang dapat dilakukan Selain Berdoa untuk mendekatkan diri kepada', 'konsep', 'hard'],
            ['MAKEDONIA', 'Tempat paulus diutus dan di arahkan oleh Roh kudus', 'konsep', 'hard'],
            ['PENTAKOSTA', 'Peristiwa turun nya Roh Kudus meliputi Murid murid Yesus saat berdoa', 'konsep', 'hard'],
            ['TOMAS', 'Murid Yesus yang tidak percaya sampai melihat dengan mata kepalanya', 'tokoh','hard'],
            ['SILAS', 'Rekan Paulus yang ikut dipenjara dalam menjalankan misi di Filipi', 'tokoh', 'hard'],
            ['STEFANUS', 'Seorang yang dipilih sebagai Tujuh Diaken Pertama di Kitab PB', 'tokoh', 'hard'],
            ['ALTAR', 'Tempat Kudus untuk melayani Tuhan dengan kerinduan hati', 'konsep', 'hard'],
            ['KEMENYAN', 'Korban persembahan yang harum', 'konsep', 'hard'],
            ['RUT', 'Tokoh PL yang menikah dengan Boas di yerusalem', 'tokoh', 'hard'],
            ['SESAWI', 'Matius 13 : 31 Tuhan Menjelaskan Perumpamaan Biji', 'konsep', 'hard'],
            ['ALKITAB', 'Semua Firman Tuhan Yesus ditulis', 'konsep', 'hard'],
            ['MIKHA', 'Nabi PL yang menegur ketidakadilan dan menubuatkan kelahiran Mesias di Betlehem', 'tokoh', 'hard'],
            ['NAHUM', 'Nabi PL yang menubuatkan kejatuhan kota Niniwe','konsep','hard'],
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
