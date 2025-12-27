<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PuzzleController extends Controller
{
    public function index()
{
    // ambil file di storage/public/puzzles
    $diskFiles = \Storage::disk('public')->files('puzzles');

    // Mapping manual: nama katalog -> filename (jika mau kendali penuh)
    // Anda boleh tambahkan entri baru di array ini.
    $catalog = [
        'Elia'   => 'puzzles/gagak.jpg',
        'Yakub' => 'puzzles/yakub.jpg',
        'Elisa'  => 'puzzles/elisa.jpg',
        // tambah sesuai file Anda...
    ];

    // Hanya sertakan item yang file-nya memang ada
    $images = [];
    foreach($catalog as $label => $path) {
        if(in_array($path, $diskFiles)) {
            $images[] = ['label' => $label, 'path' => $path];
        }
    }

    // Fallback: kalau catalog kosong / file tidak ada, pakai semua file di folder
    if(empty($images)) {
        foreach($diskFiles as $f){
            if(preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f)){
                $images[] = ['label' => basename($f), 'path' => $f];
            }
        }
    }

    return view('puzzle.index', ['images' => $images]);
}

}
