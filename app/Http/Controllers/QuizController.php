<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;
use App\Choice;
use App\Attempt;

class QuizController extends Controller
{
    use App\Category;

public function index()
{
    $quizCategory = Category::where('slug', 'quiz')->firstOrFail();

    $questions = Question::with('choices')
        ->where('category_id', $quizCategory->id) // 🔥 FILTER
        ->inRandomOrder()
        ->limit(10)
        ->get();

    $payload = $questions->map(function($q){
        return [
            'id' => $q->id,
            'prompt' => $q->prompt,
            'image_url' => $q->image_path 
                ? asset($q->image_path)   // questions/image
                : null,
            'time_limit_seconds' => $q->time_limit_seconds,
            'choices' => $q->choices->map(fn($c)=>[
                'id'=>$c->id,
                'text'=>$c->text
            ]),
            'explanation' => $q->explanation,
        ];
    });

    return view('quiz.index', compact('payload'));
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
