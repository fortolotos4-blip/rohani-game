<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $catId = DB::table('categories')->insertGetId([
            'slug' => 'quiz-rohani',
            'name' => 'Quiz Rohani',
            'description' => 'Soal Alkitab pilihan ganda',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ========== SOAL 1 ==========
        $q1 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Siapakah nama tokoh yang telah mengalahkan singa dengan mudah dan menjadi hakim atas bangsa yahudi ?',
            'answer_text' => 'Simson',
            'explanation' => 'Simson yang dipanggil Allah menjadi hakim dan mempunyai kekuatan super.',
            'image_path' => 'questions/simson.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q1, 'text'=>'Petrus', 'is_correct'=>0],
            ['question_id'=>$q1, 'text'=>'Simson', 'is_correct'=>1],
            ['question_id'=>$q1, 'text'=>'Samuel', 'is_correct'=>0],
            ['question_id'=>$q1, 'text'=>'Yosua', 'is_correct'=>0],
        ]);

        // ========== SOAL 2 ==========
        $q2 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Tuhan Yesus berpuasa di padang gurun selama ?',
            'answer_text' => '40 hari',
            'explanation' => 'Yesus berpuasa selama 40 Hari di padang gurun tanpa makan dan minum.',
            'image_path' => 'questions/yesus.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q2, 'text'=>'40 hari', 'is_correct'=>1],
            ['question_id'=>$q2, 'text'=>'10 hari', 'is_correct'=>0],
            ['question_id'=>$q2, 'text'=>'3 hari', 'is_correct'=>0],
            ['question_id'=>$q2, 'text'=>'5 hari', 'is_correct'=>0],
        ]);

        // ========== SOAL 3 ==========
        $q3 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Siapa yang menuliskan kitab mazmur ?',
            'answer_text' => 'Daud',
            'explanation' => 'Daud menulis kitab mazmur yang mencerminkan kehidupan nya.',
            'image_path' => 'questions/daud.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q3, 'text'=>'Daud', 'is_correct'=>1],
            ['question_id'=>$q3, 'text'=>'Saul', 'is_correct'=>0],
            ['question_id'=>$q3, 'text'=>'Salomo', 'is_correct'=>0],
            ['question_id'=>$q3, 'text'=>'Yehu', 'is_correct'=>0],
        ]);

        // ========== SOAL 4 ==========
        $q4 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Siapakah yang telah diangkat Tuhan dengan menggunakan kereta berapi ?',
            'answer_text' => 'Elia',
            'explanation' => 'Elia berfirman kepada elisa bahwa akan diangkat Tuhan.',
            'image_path' => 'questions/elia.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q4, 'text'=>'Elisa', 'is_correct'=>0],
            ['question_id'=>$q4, 'text'=>'Musa', 'is_correct'=>0],
            ['question_id'=>$q4, 'text'=>'Elia', 'is_correct'=>1],
            ['question_id'=>$q4, 'text'=>'Henokh', 'is_correct'=>0],
        ]);

        // ========== SOAL 5 ==========
        $q5 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Siapakah nama tokoh berikut yang berpuasa dengan tidak memakan daging ?',
            'answer_text' => 'Daniel',
            'explanation' => 'Elia berfirman kepada elisa bahwa akan diangkat Tuhan.',
            'image_path' => 'questions/daniel.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q5, 'text'=>'Ruben', 'is_correct'=>0],
            ['question_id'=>$q5, 'text'=>'Yusuf', 'is_correct'=>0],
            ['question_id'=>$q5, 'text'=>'Samuel', 'is_correct'=>0],
            ['question_id'=>$q5, 'text'=>'Daniel', 'is_correct'=>1],
        ]);

        // ========== SOAL 6 ==========
        $q6 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Pada hari apakah murid murid memetik gandum, sehingga orang farisi marah padanya ?',
            'answer_text' => 'Sabat',
            'explanation' => 'pada hari sabat, orang farisi menganggap hari itu suci sehingga tidak boleh melakukan aktivitas apapun.',
            'image_path' => 'questions/gandum.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q6, 'text'=>'Sabat', 'is_correct'=>1],
            ['question_id'=>$q6, 'text'=>'Paskah', 'is_correct'=>0],
            ['question_id'=>$q6, 'text'=>'Minggu', 'is_correct'=>0],
            ['question_id'=>$q6, 'text'=>'Jumat', 'is_correct'=>0],
        ]);

        // ========== SOAL 7 ==========
        $q7 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Siapa Raja yang berhikmat yang dapat memutuskan tindakan dalam perkara bayi pada dua perempuan?',
            'answer_text' => 'Salomo',
            'explanation' => 'Salomo memiliki hikmat melebihi dari manusia sehingga dapat memutuskan tindakan dengan benar.',
            'image_path' => 'questions/salomo.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q7, 'text'=>'Salomo', 'is_correct'=>1],
            ['question_id'=>$q7, 'text'=>'Hizkia', 'is_correct'=>0],
            ['question_id'=>$q7, 'text'=>'Daud', 'is_correct'=>0],
            ['question_id'=>$q7, 'text'=>'Saul', 'is_correct'=>0],
        ]);

                // ========== SOAL 8 ==========
        $q8 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Bangsa manakah yang disertai Tuhan untuk menghancurkan tembok Yerikho ?',
            'answer_text' => 'Israel',
            'explanation' => 'Bangsa israel disertai Tuhan untuk menghancurkan tembok Yerikho dengan memutarinya selama 7 hari.',
            'image_path' => 'questions/israel.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q8, 'text'=>'Simeon', 'is_correct'=>0],
            ['question_id'=>$q8, 'text'=>'Naftali', 'is_correct'=>0],
            ['question_id'=>$q8, 'text'=>'Lewi', 'is_correct'=>0],
            ['question_id'=>$q8, 'text'=>'Israel', 'is_correct'=>1],
        ]);

        // ========== SOAL 9 ==========
        $q9 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Tulah ke berapa yang diberikan musa pada bangsa mesir untuk mendatangkan katak ?',
            'answer_text' => 'Dua',
            'explanation' => 'Tulah kedua, musa menulahi bangsa mesir dengan katak agar raja firaun membebaskan bangsa israel dari perbudakan.',
            'image_path' => 'questions/katak.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q9, 'text'=>'Satu', 'is_correct'=>0],
            ['question_id'=>$q9, 'text'=>'Dua', 'is_correct'=>1],
            ['question_id'=>$q9, 'text'=>'Tiga', 'is_correct'=>0],
            ['question_id'=>$q9, 'text'=>'Empat', 'is_correct'=>0],
        ]);

        // ========== SOAL 10 ==========
        $q10 = DB::table('questions')->insertGetId([
            'category_id' => $catId,
            'prompt' => 'Kepada siapa Tuhan Yesus memperlihatkan Musa dan Elia?',
            'answer_text' => 'Petrus, Yakobus dan Yohanes',
            'explanation' => 'Salomo memiliki hikmat melebihi dari manusia sehingga dapat memutuskan tindakan dengan benar.',
            'image_path' => 'questions/ketigamurid.jpg',
            'time_limit_seconds' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('choices')->insert([
            ['question_id'=>$q10, 'text'=>'Sadrakh, Mesakh dan Abednego', 'is_correct'=>0],
            ['question_id'=>$q10, 'text'=>'Ketiga orang Majus', 'is_correct'=>0],
            ['question_id'=>$q10, 'text'=>'Petrus, Yakobus dan Yohanes', 'is_correct'=>1],
            ['question_id'=>$q10, 'text'=>'Thomas, Yohanes dan Petrus', 'is_correct'=>0],
        ]);
    }
}
