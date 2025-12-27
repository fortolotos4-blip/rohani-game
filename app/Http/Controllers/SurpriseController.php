<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurpriseController extends Controller
{
    // show page
    public function index()
    {
        // ambil semua surprises; akan dipilih acak di client
        $surprises = DB::table('surprises')->get()->map(function($s){
            return [
                'id' => $s->id,
                'verse' => $s->verse,
                'action_text' => $s->action_text,
            ];
        })->toArray();

        return view('surprise.index', ['surprises' => $surprises]);
    }

    // optional: rekam hasil pilihan: siapa memilih apa (untuk statistik)
    public function record(Request $r)
    {
        $r->validate([
            'surprise_id' => 'required|integer',
            'round' => 'required|integer',
            'choice' => 'required|string'
        ]);

        // simpan ke table attempts jika mau; contoh sederhana:
        DB::table('surprise_attempts')->insert([
            'surprise_id' => $r->surprise_id,
            'choice' => $r->choice,
            'round' => $r->round,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok'=>true]);
    }
}
