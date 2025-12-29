<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;
use App\Choice;
use App\Attempt;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        session()->forget('quiz_used_question_ids');
        
        // 1️⃣ Ambil ID soal yang SUDAH pernah ditampilkan
        $usedIds = session()->get('quiz_used_question_ids', []);

        // 2️⃣ Query soal quiz (HANYA dari folder questions/)
        $questions = Question::with('choices')
            ->where('image_path', 'like', 'questions/%')
            ->whereNotIn('id', $usedIds)      // 🔥 JANGAN DUPLIKAT
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // 3️⃣ Simpan ID soal yang baru dipakai ke session
        session()->put(
            'quiz_used_question_ids',
            array_merge($usedIds, $questions->pluck('id')->toArray())
        );

        // 4️⃣ Payload ke view
        $payload = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'prompt' => $q->prompt,
                'image_url' => $q->image_path
                    ? asset($q->image_path)
                    : null,
                'time_limit_seconds' => $q->time_limit_seconds ?? 15,
                'choices' => $q->choices->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'text' => $c->text,
                    ];
                })->values(),
                'explanation' => $q->explanation,
            ];
        })->toArray();

        return view('quiz.index', [
            'questions' => $payload,
        ]);
    }


    // endpoint tetap pakai untuk validasi jawaban dan menyimpan attempt
    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|integer',
        ]);

        $question = Question::find($request->question_id);
        if(!$question){
            return response()->json(['error'=>'Pertanyaan tidak ditemukan'], 404);
        }

        $choice = null;
        $correct = false;

        if($request->choice_id){
            $choice = Choice::find($request->choice_id);
            if($choice && $choice->question_id != $question->id){
                $choice = null;
            }
        }

        if($choice){
            $correct = (bool)$choice->is_correct;
        }

        // simpan attempt sederhana (tanpa user)
        Attempt::create([
            'question_id' => $question->id,
            'user_id' => auth()->id() ?? null,
            'choice_id' => $choice ? $choice->id : null,
            'correct' => $correct,
            'time_taken_seconds' => $request->time_taken_seconds ?? null,
        ]);

        return response()->json([
            'correct' => $correct,
            'correct_answer' => $question->answer_text,
            'explanation' => $question->explanation,
        ]);
    }
}
