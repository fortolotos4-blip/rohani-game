<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Question;

class MultiplayerController extends Controller
{
    /* =========================================================
     * UTIL
     * =======================================================*/

    private function normalize(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $value));
    }

    private function currentPlayerId(): ?int
    {
        return session('multiplayer_player_id');
    }

    /* =========================================================
     * ROOM
     * =======================================================*/

    public function createRoom(Request $request)
    {
        $request->validate([
            'player_name'  => 'required|string|max:30',
            'max_players'  => 'required|integer|min:2|max:4',
        ]);

        $roomCode = strtoupper(substr(md5(uniqid()), 0, 6));

        DB::beginTransaction();

        $roomId = DB::table('multiplayer_rooms')->insertGetId([
            'room_code' => $roomCode,
            'max_players' => $request->max_players,
            'status' => 'waiting',
            'current_question_index' => 0,
            'turn_locked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $playerId = DB::table('multiplayer_room_players')->insertGetId([
            'room_id' => $roomId,
            'player_name' => $request->player_name,
            'color' => 'blue',
            'score' => 0,
            'joined_at' => now(),
        ]);

        session(['multiplayer_player_id' => $playerId]);

        DB::commit();

        return response()->json([
            'room_code' => $roomCode,
            'player_id' => $playerId,
        ]);
    }

    public function joinRoom(Request $request)
    {
        $request->validate([
            'room_code' => 'required|string',
            'player_name' => 'required|string|max:30',
        ]);

        $room = DB::table('multiplayer_rooms')
            ->where('room_code', $request->room_code)
            ->first();

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        if ($room->status !== 'waiting') {
            return response()->json(['error' => 'Room already started'], 409);
        }

        $count = DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->count();

        if ($count >= $room->max_players) {
            return response()->json(['error' => 'Room full'], 403);
        }

        $colors = ['blue', 'red', 'orange', 'green'];

        $playerId = DB::table('multiplayer_room_players')->insertGetId([
            'room_id' => $room->id,
            'player_name' => $request->player_name,
            'color' => $colors[$count],
            'score' => 0,
            'joined_at' => now(),
        ]);

        session(['multiplayer_player_id' => $playerId]);

        $countAfter = $count + 1;

        if ($countAfter == $room->max_players) {

    $players = DB::table('multiplayer_room_players')
        ->where('room_id', $room->id)
        ->pluck('id')
        ->toArray();

    shuffle($players);

    foreach ($players as $i => $pid) {
        DB::table('multiplayer_room_players')
            ->where('id', $pid)
            ->update(['turn_order' => $i + 1]);
    }

    DB::table('multiplayer_rooms')
        ->where('id', $room->id)
        ->update([
            'status' => 'playing',
            'current_turn_player_id' => $players[0],
            'turn_started_at' => now(),
            'turn_locked' => false,
        ]);
}

        return response()->json([
            'success' => true,
            'player_id' => $playerId,
        ]);
    }

    public function roomState(string $code)
{
    $room = DB::table('multiplayer_rooms')
        ->where('room_code', $code)
        ->first();

    if (!$room) {
        return response()->json(['error' => 'Room not found'], 404);
    }

    $players = DB::table('multiplayer_room_players')
        ->where('room_id', $room->id)
        ->orderBy('turn_order')
        ->get();

    return response()->json([
        'room' => $room,
        'players' => $players,
    ]);
}

    /* =========================================================
     * GAME STATE (POLLING)
     * =======================================================*/

    public function gameState(string $code)
{
    $room = DB::table('multiplayer_rooms')
        ->where('room_code', $code)
        ->first();

    if (!$room) {
        return response()->json(['error' => 'Room not found'], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | GAME OVER CHECK (SATU KALI, DI AWAL)
    |--------------------------------------------------------------------------
    */
    if (
        $room->status === 'playing' &&
        $room->game_started_at &&
        now()->diffInSeconds($room->game_started_at) >= 350
    ) {
        DB::table('multiplayer_rooms')
            ->where('id', $room->id)
            ->update(['status' => 'finished']);

        $room->status = 'finished';
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO SKIP TURN (HANYA JIKA MASIH PLAYING)
    |--------------------------------------------------------------------------
    */
    if (
        $room->status === 'playing' &&
        !$room->turn_locked &&
        $room->turn_started_at &&
        now()->diffInSeconds($room->turn_started_at) >= 30
    ) {
        // lock agar tidak double skip
        $locked = DB::table('multiplayer_rooms')
            ->where('id', $room->id)
            ->where('turn_locked', false)
            ->update(['turn_locked' => true]);

        if ($locked) {
            $currentOrder = DB::table('multiplayer_room_players')
                ->where('id', $room->current_turn_player_id)
                ->value('turn_order');

            $nextPlayer = DB::table('multiplayer_room_players')
                ->where('room_id', $room->id)
                ->where('turn_order', '>', $currentOrder)
                ->orderBy('turn_order')
                ->first();

            if (!$nextPlayer) {
                $nextPlayer = DB::table('multiplayer_room_players')
                    ->where('room_id', $room->id)
                    ->orderBy('turn_order')
                    ->first();
            }

            DB::table('multiplayer_rooms')
                ->where('id', $room->id)
                ->update([
                    'current_turn_player_id' => $nextPlayer->id,
                    'turn_started_at' => now(),
                    'turn_locked' => false,
                ]);

            // refresh state
            $room = DB::table('multiplayer_rooms')
                ->where('id', $room->id)
                ->first();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PREPARE DATA UNTUK CLIENT
    |--------------------------------------------------------------------------
    */
    $players = DB::table('multiplayer_room_players')
        ->where('room_id', $room->id)
        ->orderBy('turn_order')
        ->get();

    $stickers = DB::table('multiplayer_stickers')
        ->where('room_code', $code)
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get()
        ->reverse()
        ->values();

    $lastValidation = $room->last_validation
        ? json_decode($room->last_validation, true)
        : null;

    $response = response()->json([
        'room_status' => $room->status,
        'current_turn_player_id' => $room->current_turn_player_id,

        // ⏱ TURN TIMER (SERVER SOURCE OF TRUTH)
        'turn_left' => $room->turn_started_at
            ? max(0, 30 - now()->diffInSeconds($room->turn_started_at))
            : null,

        // ⏳ SESSION TIMER
        'session_left' => $room->game_started_at
            ? max(0, 350 - now()->diffInSeconds($room->game_started_at))
            : null,

        'players' => $players,
        'last_validation' => $lastValidation,
        'stickers' => $stickers,
    ]);

    /*
    |--------------------------------------------------------------------------
    | CLEAR LAST VALIDATION (SETELAH DIKIRIM)
    |--------------------------------------------------------------------------
    */
    if ($room->last_validation) {
        DB::table('multiplayer_rooms')
            ->where('id', $room->id)
            ->update(['last_validation' => null]);
    }

    return $response;
}

    /* =========================================================
     * ANSWER
     * =======================================================*/

    public function submitAnswer(Request $request)
{
    $request->validate([
        'room_code' => 'required',
        'answer' => 'required|string',
    ]);

    $playerId = $this->currentPlayerId();

    DB::beginTransaction();

    $room = DB::table('multiplayer_rooms')
        ->where('room_code', $request->room_code)
        ->lockForUpdate()
        ->first();

    if (!$room || $room->status !== 'playing') {
        DB::rollBack();
        return response()->json(['error' => 'Game not active'], 409);
    }

    if ($room->current_turn_player_id !== $playerId) {
        DB::rollBack();
        return response()->json(['error' => 'Not your turn'], 403);
    }

    if ($room->turn_locked) {
        DB::rollBack();
        return response()->json(['error' => 'Turn locked'], 409);
    }

    // 🔒 LOCK TURN
    DB::table('multiplayer_rooms')
        ->where('id', $room->id)
        ->update(['turn_locked' => true]);

    // 📘 AMBIL SOAL
    $question = Question::skip($room->current_question_index)->first();

    $correct =
        $this->normalize($request->answer) ===
        $this->normalize($question->answer_text);

    // 📝 SIMPAN VALIDASI
    DB::table('multiplayer_rooms')
        ->where('id', $room->id)
        ->update([
            'last_validation' => json_encode([
                'player_id' => $playerId,
                'correct' => $correct,
                'answer' => $request->answer,
            ]),
            'updated_at' => now(),
        ]);

    // 🎯 JIKA BENAR → TAMBAH SKOR & SOAL
    if ($correct) {
        DB::table('multiplayer_room_players')
            ->where('id', $playerId)
            ->increment('score');

        DB::table('multiplayer_rooms')
            ->where('id', $room->id)
            ->update([
                'current_question_index' => $room->current_question_index + 1,
            ]);
    }

    // 🔁 ROTASI GILIRAN (PAKAI turn_order)
    $currentOrder = DB::table('multiplayer_room_players')
        ->where('id', $playerId)
        ->value('turn_order');

    $nextPlayer = DB::table('multiplayer_room_players')
        ->where('room_id', $room->id)
        ->where('turn_order', '>', $currentOrder)
        ->orderBy('turn_order')
        ->first();

    if (!$nextPlayer) {
        $nextPlayer = DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->orderBy('turn_order')
            ->first();
    }

    // ▶️ PINDAH GILIRAN
    DB::table('multiplayer_rooms')
        ->where('id', $room->id)
        ->update([
            'current_turn_player_id' => $nextPlayer->id,
            'turn_started_at' => now(),
            'turn_locked' => false,
        ]);

    DB::commit();

    return response()->json(['correct' => $correct]);
}


    /* =========================================================
     * STICKER
     * =======================================================*/

    public function sendSticker(Request $request)
    {
        $request->validate([
            'room_code' => 'required',
            'sticker' => 'required|string|max:20',
        ]);

        $playerId = $this->currentPlayerId();

        $last = DB::table('multiplayer_stickers')
            ->where('player_id', $playerId)
            ->latest()
            ->first();

        if ($last && now()->diffInSeconds($last->created_at) < 20) {
            return response()->json(['error' => 'Cooldown'], 429);
        }

        DB::table('multiplayer_stickers')->insert([
            'room_code' => $request->room_code,
            'player_id' => $playerId,
            'sticker' => $request->sticker,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
