<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PuzzleController extends Controller
{
    public function index()
{
    // path absolut ke folder public/puzzles
    $path = public_path('puzzles');

    $images = [];

    if (is_dir($path)) {
        $files = scandir($path);

        foreach ($files as $file) {
            if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $file)) {
                $images[] = [
                    'label' => pathinfo($file, PATHINFO_FILENAME),
                    'path'  => 'puzzles/' . $file, // RELATIVE ke public
                ];
            }
        }
    }

    return view('puzzle.index', compact('images'));
}


}
