<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | CATEGORY (AMAN, TIDAK DUPLIKAT)
        |--------------------------------------------------------------------------
        */
        $category = DB::table('categories')
            ->where('slug', 'quiz-rohani')
            ->first();

        if (!$category) {
            $catId = DB::table('categories')->insertGetId([
                'slug' => 'quiz-rohani',
                'name' => 'Quiz Rohani',
                'description' => 'Soal Alkitab pilihan ganda',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $catId = $category->id;
        }

        /*
        |--------------------------------------------------------------------------
        | DATA SOAL
        |--------------------------------------------------------------------------
        */
        $questions = [
            [
                'prompt' => 'Siapakah nama tokoh yang telah mengalahkan singa dengan mudah dan menjadi hakim atas bangsa yahudi ?',
                'answer_text' => 'Simson',
                'explanation' => 'Simson yang dipanggil Allah menjadi hakim dan mempunyai kekuatan super.',
                'image_path' => 'questions/simson.jpg',
                'choices' => [
                    ['Petrus', 0],
                    ['Simson', 1],
                    ['Samuel', 0],
                    ['Yosua', 0],
                ]
            ],
            [
                'prompt' => 'Tuhan Yesus berpuasa di padang gurun selama ?',
                'answer_text' => '40 hari',
                'explanation' => 'Yesus berpuasa selama 40 Hari di padang gurun tanpa makan dan minum.',
                'image_path' => 'questions/yesus.jpg',
                'choices' => [
                    ['40 hari', 1],
                    ['10 hari', 0],
                    ['3 hari', 0],
                    ['5 hari', 0],
                ]
            ],
            [
                'prompt' => 'Siapa yang menuliskan kitab mazmur ?',
                'answer_text' => 'Daud',
                'explanation' => 'Daud menulis kitab mazmur yang mencerminkan kehidupan nya.',
                'image_path' => 'questions/daud.jpg',
                'choices' => [
                    ['Daud', 1],
                    ['Saul', 0],
                    ['Salomo', 0],
                    ['Yehu', 0],
                ]
            ],
            [
                'prompt' => 'Siapakah yang telah diangkat Tuhan dengan menggunakan kereta berapi ?',
                'answer_text' => 'Elia',
                'explanation' => 'Elia berfirman kepada elisa bahwa akan diangkat Tuhan.',
                'image_path' => 'questions/elia.jpg',
                'choices' => [
                    ['Elisa', 0],
                    ['Musa', 0],
                    ['Elia', 1],
                    ['Henokh', 0],
                ]
            ],
            [
                'prompt' => 'Siapakah nama tokoh berikut yang berpuasa dengan tidak memakan daging ?',
                'answer_text' => 'Daniel',
                'explanation' => 'Daniel berpuasa dengan tidak memakan makanan yang lezat.',
                'image_path' => 'questions/daniel.jpg',
                'choices' => [
                    ['Ruben', 0],
                    ['Yusuf', 0],
                    ['Samuel', 0],
                    ['Daniel', 1],
                ]
            ],
            [
                'prompt' => 'Pada hari apakah murid murid memetik gandum, sehingga orang farisi marah padanya ?',
                'answer_text' => 'Sabat',
                'explanation' => 'Orang Farisi marah karena hal itu terjadi pada hari Sabat.',
                'image_path' => 'questions/gandum.jpg',
                'choices' => [
                    ['Sabat', 1],
                    ['Paskah', 0],
                    ['Minggu', 0],
                    ['Jumat', 0],
                ]
            ],
            [
                'prompt' => 'Siapa Raja yang berhikmat yang dapat memutuskan tindakan dalam perkara bayi pada dua perempuan?',
                'answer_text' => 'Salomo',
                'explanation' => 'Salomo memiliki hikmat yang besar dari Tuhan.',
                'image_path' => 'questions/salomo.jpg',
                'choices' => [
                    ['Salomo', 1],
                    ['Hizkia', 0],
                    ['Daud', 0],
                    ['Saul', 0],
                ]
            ],
            [
                'prompt' => 'Bangsa manakah yang disertai Tuhan untuk menghancurkan tembok Yerikho ?',
                'answer_text' => 'Israel',
                'explanation' => 'Bangsa Israel mengelilingi tembok Yerikho selama 7 hari.',
                'image_path' => 'questions/israel.jpg',
                'choices' => [
                    ['Simeon', 0],
                    ['Naftali', 0],
                    ['Lewi', 0],
                    ['Israel', 1],
                ]
            ],
            [
                'prompt' => 'Tulah ke berapa yang diberikan musa pada bangsa mesir untuk mendatangkan katak ?',
                'answer_text' => 'Dua',
                'explanation' => 'Katak adalah tulah kedua.',
                'image_path' => 'questions/katak.jpg',
                'choices' => [
                    ['Satu', 0],
                    ['Dua', 1],
                    ['Tiga', 0],
                    ['Empat', 0],
                ]
            ],
            [
                'prompt' => 'Kepada siapa Tuhan Yesus memperlihatkan Musa dan Elia?',
                'answer_text' => 'Petrus, Yakobus dan Yohanes',
                'explanation' => 'Yesus menampakkan diri di atas gunung.',
                'image_path' => 'questions/ketigamurid.jpg',
                'choices' => [
                    ['Sadrakh, Mesakh dan Abednego', 0],
                    ['Ketiga orang Majus', 0],
                    ['Petrus, Yakobus dan Yohanes', 1],
                    ['Thomas, Yohanes dan Petrus', 0],
                ]
            ],
            [
                'prompt' => 'Kepada siapa Tuhan Yesus memperlihatkan Musa dan Elia?',
                'answer_text' => 'Petrus, Yakobus dan Yohanes',
                'explanation' => 'Yesus menampakkan diri di atas gunung.',
                'image_path' => 'questions/ketigamurid.jpg',
                'choices' => [
                    ['Sadrakh, Mesakh dan Abednego', 0],
                    ['Ketiga orang Majus', 0],
                    ['Petrus, Yakobus dan Yohanes', 1],
                    ['Thomas, Yohanes dan Petrus', 0],
                ]
            ],
            [
                'prompt' => 'Kepada siapa Tuhan Yesus memperlihatkan Musa dan Elia?',
                'answer_text' => 'Petrus, Yakobus dan Yohanes',
                'explanation' => 'Yesus menampakkan diri di atas gunung.',
                'image_path' => 'questions/ketigamurid.jpg',
                'choices' => [
                    ['Sadrakh, Mesakh dan Abednego', 0],
                    ['Ketiga orang Majus', 0],
                    ['Petrus, Yakobus dan Yohanes', 1],
                    ['Thomas, Yohanes dan Petrus', 0],
                ]
            ],
            [
                'prompt' => 'Kepada siapa Tuhan Yesus memperlihatkan Musa dan Elia?',
                'answer_text' => 'Petrus, Yakobus dan Yohanes',
                'explanation' => 'Yesus menampakkan diri di atas gunung.',
                'image_path' => 'questions/ketigamurid.jpg',
                'choices' => [
                    ['Sadrakh, Mesakh dan Abednego', 0],
                    ['Ketiga orang Majus', 0],
                    ['Petrus, Yakobus dan Yohanes', 1],
                    ['Thomas, Yohanes dan Petrus', 0],
                ]
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | INSERT SOAL & PILIHAN (AMAN)
        |--------------------------------------------------------------------------
        */
        foreach ($questions as $q) {

            $question = DB::table('questions')
                ->where('prompt', $q['prompt'])
                ->first();

            if (!$question) {
                $questionId = DB::table('questions')->insertGetId([
                    'category_id' => $catId,
                    'prompt' => $q['prompt'],
                    'answer_text' => $q['answer_text'],
                    'explanation' => $q['explanation'],
                    'image_path' => $q['image_path'],
                    'time_limit_seconds' => 16,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $questionId = $question->id;
            }

            foreach ($q['choices'] as [$text, $correct]) {
                DB::table('choices')->updateOrInsert(
                    [
                        'question_id' => $questionId,
                        'text' => $text,
                    ],
                    [
                        'is_correct' => $correct,
                    ]
                );
            }
        }
    }
}
