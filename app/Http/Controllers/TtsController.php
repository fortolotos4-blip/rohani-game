<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Tts\HybridTtsGenerator;

class TtsController extends Controller
{
    public function index(Request $request)
    {
        $difficulty = $request->get('difficulty', 'easy');

        $generator = new HybridTtsGenerator(10);
        $puzzle = $generator->generate($difficulty);

        return view('tts.index', [
            'puzzle' => $puzzle,
            'difficulty' => $difficulty,
        ]);
    }
}
