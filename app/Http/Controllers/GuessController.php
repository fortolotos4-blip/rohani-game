<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;

class GuessController extends Controller
{
    // Menu page
    public function menu()
    {
        return view('guess.menu');
    }

    // Single mode: pick N random questions and send to view
    public function single()
{
    $questions = Question::where('image_path', 'like', 'guess/%')
        ->inRandomOrder()
        ->get()
        ->map(function ($q) {
            return [
                'id' => $q->id,
                'image_path' => $q->image_path,
                'answer_text' => $q->answer_text,
                'answer_slots' => $q->answer_slots,
                'time_limit_seconds' => $q->time_limit_seconds ?? 60,
            ];
        })
        ->toArray();

    return view('guess.single', compact('questions'));
}


    // Validate single answer (AJAX)
    public function singleAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'nullable|string',
            'time_taken_seconds' => 'nullable|integer',
        ]);

        $q = Question::find($request->question_id);
        if(!$q) return response()->json(['error'=>'Question not found'],404);

        $provided = trim($request->answer ?? '');
        // normalize: remove spaces, lowercase
        $isCorrect = mb_strtolower(preg_replace('/\s+/', '', $provided)) === mb_strtolower(preg_replace('/\s+/', '', $q->answer_text));

        return response()->json([
            'correct' => $isCorrect,
            'correct_answer' => $q->answer_text,
        ]);
    }

    // Duo mode: supply questions array
    public function duo()
{
    $questions = Question::where('image_path', 'like', 'guess/%')
        ->inRandomOrder()
        ->limit(10)
        ->get()
        ->map(function ($q) {
            return [
                'id' => $q->id,
                'image_path' => $q->image_path,
                'answer_text' => $q->answer_text,
                'answer_slots' => $q->answer_slots,
                'time_limit_seconds' => $q->time_limit_seconds ?? 16,
            ];
        })
        ->toArray();

    return view('guess.duo', compact('questions'));
}


    // Duo answer — similar validation
    public function duoAnswer(Request $request)
{
    $request->validate([
        'question_id' => 'required|integer',
        'answer' => 'nullable|string',
        'player' => 'required|string|in:A,B', // 🔥 FIX
    ]);

    $q = Question::find($request->question_id);
    if (!$q) {
        return response()->json(['error' => 'Question not found'], 404);
    }

    // 🔥 NORMALISASI IDENTIK DENGAN FRONTEND
    $normalize = function ($str) {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $str));
    };

    $isCorrect = $normalize($request->answer ?? '') === $normalize($q->answer_text);

    return response()->json([
        'correct' => $isCorrect,
        'correct_answer' => $q->answer_text,
    ]);
}
}
