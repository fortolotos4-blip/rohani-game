<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Tts\HybridTtsGenerator;

class TtsRoomController extends Controller
{
    /* =========================
       CREATE ROOM
    ========================== */
    public function create(Request $request)
{
    $request->validate([
        'player' => 'required|string'
    ]);

    $code = strtoupper(Str::random(6));

    // 🔒 GUARD: pastikan ada puzzle di tts_puzzles
    $puzzleId = DB::table('tts_puzzles')->min('id');

    if (!$puzzleId) {
    return response()->json([
        'error' => 'tts_puzzles table is empty on Render database'
    ], 500);
}

    DB::table('tts_rooms')->insert([
        'room_code' => $code,
        'player1' => $request->player,
        'status' => 'waiting',
        'player1_score' => 0,
        'player2_score' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('tts.room.play', [
        'code' => $code,
        'player' => $request->player
    ]);
}


    /* =========================
       JOIN ROOM
    ========================== */
    public function join(Request $request)
    {
        $request->validate([
            'room_code' => 'required',
            'player' => 'required'
        ]);

        $room = DB::table('tts_rooms')
            ->where('room_code', strtoupper($request->room_code))
            ->first();

        if (!$room) {
            return back()->withErrors(['room_code' => 'Room tidak ditemukan']);
        }

        if ($room->player2) {
            return back()->withErrors(['room_code' => 'Room penuh']);
        }

        $puzzle = (new HybridTtsGenerator(10))->generateMixed();

        DB::table('tts_rooms')->where('id', $room->id)->update([
            'player2' => $request->player,
            'status' => 'rps',
            'puzzle_json' => json_encode($puzzle),
            'updated_at' => now()
        ]);

        return redirect()->route('tts.room.play', [
            'code' => $room->room_code,
            'player' => $request->player,
            'force_rps' => 1
        ]);
    }

    /* =========================
       PLAY ROOM
    ========================== */
    public function play($code)
    {
        $room = DB::table('tts_rooms')->where('room_code', $code)->first();
        abort_if(!$room, 404);

        return view('tts.multiplayer-play', [
            'room' => $room,
            'player' => request('player')
        ]);
    }

    public function cells($code)
{
    return DB::table('tts_room_cells')
        ->where('room_code', $code)
        ->get();
}

    /* =========================
       REALTIME STATE
    ========================== */
   public function state($code)
{
    $room = DB::table('tts_rooms')->where('room_code', $code)->first();
    if (!$room) {
        return response()->json(['error' => true], 404);
    }

    // ===============================
    // HANYA JALAN SAAT PLAYING
    // ===============================
    if ($room->status === 'playing') {

        // ⏱️ SAFE CHECK TURN TIMER
        if ($room->turn_started_at &&
            now()->diffInSeconds($room->turn_started_at) >= 60) {
            $this->forceEndTurn($room);
        }

        // ⏱️ SAFE CHECK GAME TIMER
        if ($room->game_started_at &&
            now()->diffInSeconds($room->game_started_at) >= 480) {
            DB::table('tts_rooms')
                ->where('id', $room->id)
                ->update(['status' => 'finished']);
        }

        // 🔐 CHECK SEMUA KATA SUDAH TERKUNCI
        if ($room->puzzle_json) {
            $puzzle = json_decode($room->puzzle_json, true);
            $totalWords = count($puzzle['entries'] ?? []);

            $lockedWords = DB::table('tts_room_words')
                ->where('room_code', $room->room_code)
                ->count();

            if ($totalWords > 0 && $lockedWords >= $totalWords) {
                DB::table('tts_rooms')
                    ->where('id', $room->id)
                    ->update(['status' => 'finished']);
            }
        }
    }

    // ===============================
    // RESPONSE SELALU AMAN
    // ===============================
    return response()->json([
        'status' => $room->status,
        'player1' => $room->player1,
        'player2' => $room->player2,
        'current_turn' => $room->current_turn,
        'game_time' => max(
            0,
            $room->game_started_at
                ? 480 - now()->diffInSeconds($room->game_started_at)
                : 480
        ),
        'turn_time' => max(
            0,
            $room->turn_started_at
                ? 60 - now()->diffInSeconds($room->turn_started_at)
                : 60
        ),
        'score1' => $room->player1_score,
        'score2' => $room->player2_score,
    ]);
}


    /* =========================
       RPS
    ========================== */
    public function rpsSubmit(Request $request, $code)
    {
        $request->validate([
            'choice' => 'required|in:rock,paper,scissors',
            'player' => 'required'
        ]);

        $room = DB::table('tts_rooms')->where('room_code', $code)->first();
        if (!$room || $room->status !== 'rps') {
            return response()->json(['ok' => false]);
        }

        $field = $request->player === $room->player1 ? 'rps_p1' : 'rps_p2';

        DB::table('tts_rooms')->where('id', $room->id)->update([
            $field => $request->choice
        ]);

        $room = DB::table('tts_rooms')->where('id', $room->id)->first();

        if (!$room->rps_p1 || !$room->rps_p2) {
            return response()->json(['waiting' => true]);
        }

        $winner = $this->decideRpsWinner(
            $room->player1, $room->rps_p1,
            $room->player2, $room->rps_p2
        );

        DB::table('tts_rooms')->where('id', $room->id)->update([
            'status' => 'playing',
            'current_turn' => $winner,
            'game_started_at' => now(),
            'turn_started_at' => now()
        ]);

        return response()->json([
    'status' => 'playing',
    'first_turn' => $winner
]);

    }

    /* =========================
       UPDATE CELL
    ========================== */
    public function updateCell(Request $request, $code)
    {
        $room = DB::table('tts_rooms')->where('room_code', $code)->first();

        if (!$room || $room->status !== 'playing') {
            return response()->json(['ok' => false], 403);
        }

        if ($room->current_turn !== $request->player) {
            return response()->json(['ok' => false], 403);
        }

        DB::table('tts_room_cells')->updateOrInsert(
            [
                'room_code' => $code,
                'x' => $request->x,
                'y' => $request->y,
            ],
            [
                'letter' => strtoupper($request->letter),
                'locked' => false,
                'locked_by' => $request->player,
                'updated_at' => now()
            ]
        );

        return response()->json(['ok' => true]);
    }

    /* =========================
       CHECK WORD
    ========================== */
    public function checkWord(Request $request, $code)
{
    return DB::transaction(function () use ($request, $code) {

        $room = DB::table('tts_rooms')->where('room_code', $code)->lockForUpdate()->first();
        if (!$room || $room->current_turn !== $request->player) {
            return response()->json(['status' => 'forbidden']);
        }

        $puzzle = json_decode($room->puzzle_json, true);
        $entry  = $puzzle['entries'][$request->word_index];

        $answer = collect($request->cells)
            ->map(fn($c) => strtoupper($c['letter']))
            ->implode('');

        if ($answer !== strtoupper($entry['word'])) {
            return response()->json(['status' => 'wrong']);
        }

        // cegah double lock
        if (
            DB::table('tts_room_words')
                ->where('room_code', $code)
                ->where('word_index', $request->word_index)
                ->exists()
        ) {
            return response()->json(['status' => 'locked']);
        }

        DB::table('tts_room_words')->insert([
            'room_code' => $code,
            'word_index' => $request->word_index,
            'locked_by' => $request->player,
            'locked' => true,
            'created_at' => now()
        ]);

        foreach ($request->cells as $c) {
            DB::table('tts_room_cells')->updateOrInsert(
                [
                    'room_code' => $code,
                    'x' => $c['x'],
                    'y' => $c['y'],
                ],
                [
                    'letter' => strtoupper($c['letter']),
                    'locked' => true,
                    'locked_by' => $request->player,
                    'updated_at'=> now()
                ]
            );
        }

        $scoreField = $request->player === $room->player1
            ? 'player1_score'
            : 'player2_score';

        DB::table('tts_rooms')->where('id', $room->id)
            ->increment($scoreField, 10);

        $nextTurn = $room->current_turn === $room->player1
            ? $room->player2
            : $room->player1;

        DB::table('tts_rooms')->where('id', $room->id)->update([
            'current_turn' => $nextTurn,
            'turn_started_at' => now()
        ]);

        return response()->json([
            'status' => 'correct',
            'next_turn' => $nextTurn
        ]);
    });
}


    protected function forceEndTurn($room)
    {
        DB::table('tts_room_cells')
            ->where('room_code', $room->room_code)
            ->where('locked', false)
            ->where('locked_by', $room->current_turn)

            ->delete();

        $this->endTurn($room);
    }

    protected function endTurn($room)
    {
        $next = $room->current_turn === $room->player1
            ? $room->player2
            : $room->player1;

        DB::table('tts_rooms')->where('id', $room->id)->update([
            'current_turn' => $next,
            'turn_started_at' => now()
        ]);
    }

    protected function decideRpsWinner($p1, $c1, $p2, $c2)
    {
        if ($c1 === $c2) return rand(0, 1) ? $p1 : $p2;

        return in_array($c1.'-'.$c2, [
            'rock-scissors','scissors-paper','paper-rock'
        ]) ? $p1 : $p2;
    }

    public function puzzle($code)
    {
        $room = DB::table('tts_rooms')->where('room_code', $code)->first();

        if (!$room || !$room->puzzle_json) {
            return response()->json(['ready' => false]);
        }

        return response()->json([
            'ready' => true,
            'puzzle' => json_decode($room->puzzle_json, true)
        ]);
    }
}
