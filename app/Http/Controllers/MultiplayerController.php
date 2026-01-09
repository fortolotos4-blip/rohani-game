<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Question;

class MultiplayerController extends Controller
{

    private function forceNextTurn($room)
{
    DB::transaction(function () use ($room) {

        $currentOrder = DB::table('multiplayer_room_players')
            ->where('id', $room->current_turn_player_id)
            ->value('turn_order');

        $nextPlayer = DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->where('turn_order', '>', $currentOrder)
            ->orderBy('turn_order')
            ->first()
            ?? DB::table('multiplayer_room_players')
                ->where('room_id', $room->id)
                ->orderBy('turn_order')
                ->first();

        // ✅ GUARD WAJIB (EDGE CASE AMAN)
        if (!$nextPlayer) {
            return;
        }

        DB::table('multiplayer_rooms')
            ->where('id', $room->id)
            ->update([
                'current_turn_player_id' => $nextPlayer->id,
                'turn_started_at'        => now(),
                'turn_locked'            => false,
            ]);
    });
}


    /* =========================================================
     * UTIL
     * =======================================================*/
    private function normalize(string $value): string
    {
        return mb_strtolower(
            trim(preg_replace('/[^a-zA-Z0-9]/u', '', $value)),
            'UTF-8'
        );
    }


    /* =========================================================
     * CREATE ROOM
     * =======================================================*/
    public function createRoom(Request $request)
    {
        $request->validate([
            'player_name' => 'required|string|max:30',
            'max_players' => 'required|integer|min:2|max:4',
        ]);

        $roomCode = strtoupper(substr(md5(uniqid()), 0, 6));

        DB::beginTransaction();

        $roomId = DB::table('multiplayer_rooms')->insertGetId([
            'room_code'               => $roomCode,
            'max_players'             => $request->max_players,
            'status'                  => 'waiting',
            'current_question_index'  => 0,
            'turn_locked'             => false,
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);

        $playerId = DB::table('multiplayer_room_players')->insertGetId([
            'room_id'     => $roomId,
            'player_name' => $request->player_name,
            'color'       => 'blue',
            'score'       => 0,
            'joined_at'   => now(),
        ]);

        session(['multiplayer_player_id' => $playerId]);

        DB::commit();

        return response()->json([
            'room_code' => $roomCode,
            'player_id' => $playerId,
        ]);
    }

    /* =========================================================
     * JOIN ROOM
     * =======================================================*/
    public function joinRoom(Request $request)
    {
        $request->validate([
            'player_name' => 'required|string|max:30',
            'room_code'   => 'required|string',
        ]);

        DB::beginTransaction();

        $room = DB::table('multiplayer_rooms')
            ->where('room_code', $request->room_code)
            ->lockForUpdate()
            ->first();

        if (!$room) {
            DB::rollBack();
            return response()->json(['error' => 'Room not found'], 404);
        }

        if ($room->status !== 'waiting') {
            DB::rollBack();
            return response()->json(['error' => 'Game already started'], 409);
        }

        $count = DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->count();

        if ($count >= $room->max_players) {
            DB::rollBack();
            return response()->json(['error' => 'Room full'], 403);
        }

        $colors = ['blue', 'red', 'orange', 'green'];

        $playerId = DB::table('multiplayer_room_players')->insertGetId([
            'room_id'     => $room->id,
            'player_name' => $request->player_name,
            'color'       => $colors[$count],
            'score'       => 0,
            'joined_at'   => now(),
        ]);

        session(['multiplayer_player_id' => $playerId]);

        // 🚀 START GAME IF FULL
        if ($count + 1 === (int) $room->max_players) {

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
                    'status'                 => 'playing',
                    'current_turn_player_id' => $players[0],
                    'game_started_at'        => now(),
                    'turn_started_at'        => now(),
                    'turn_locked'            => false,
                ]);
            // 🔀 ACAK SOAL SEKALI SAAT GAME MULAI
            $questionIds = Question::where('image_path', 'like', 'images/%')
                ->inRandomOrder()
                ->pluck('id')
                ->toArray();

            DB::table('multiplayer_rooms')
                ->where('id', $room->id)
                ->update([
                    'question_order' => json_encode($questionIds),
                    'current_question_index' => 0,
                ]);
        }
        
        DB::commit();

        return response()->json(['success' => true]);
    }

    /* =========================================================
     * LOBBY STATE (POLLING)
     * =======================================================*/
    public function lobbyState(string $code)
    {
        $room = DB::table('multiplayer_rooms')
            ->where('room_code', $code)
            ->first();

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $players = DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->orderBy('joined_at')
            ->get();

        return response()->json([
            'room' => [
                'code'        => $room->room_code,
                'status'      => $room->status,
                'max_players' => $room->max_players,
            ],
            'players' => $players,
        ]);
    }

    /* =========================================================
     * GAME STATE (POLLING)
     * =======================================================*/
    public function gameState(string $code)
{
    $playerId = session('multiplayer_player_id');

    $room = DB::table('multiplayer_rooms')
        ->where('room_code', $code)
        ->first();

    if (!$room) {
        return response()->json(['error' => 'Room not found'], 404);
    }

    // ⏱️ FORCE TURN TIMEOUT (SERVER AUTHORITY)
    if ($room->status === 'playing' && !$room->turn_locked) {
        $elapsed = now()->diffInSeconds($room->turn_started_at);
        if ($elapsed >= 60) {
            $this->forceNextTurn($room);
            $room = DB::table('multiplayer_rooms')->where('id', $room->id)->first();
        }
    }

    $players = DB::table('multiplayer_room_players')
        ->where('room_id', $room->id)
        ->orderBy('turn_order')
        ->get();

    $questionOrder = json_decode($room->question_order, true);

    $qid = is_array($questionOrder)
        ? ($questionOrder[$room->current_question_index] ?? null)
        : null;

    $question = $qid ? Question::find($qid) : null;

    if (!$qid && $room->status === 'playing') {
    DB::table('multiplayer_rooms')
        ->where('id', $room->id)
        ->update([
            'status' => 'finished',
            'question_order' => null,
            'current_question_index' => 0,
        ]);

    return response()->json([
        'room_status' => 'finished',
        'session_left' => 0,
        'players' => $players,
        'stickers' => [],
    ]);
}

    $stickers = DB::table('multiplayer_stickers')
    ->select('player_id', 'emoji')
    ->where('room_id', $room->id)
    ->where('created_at', '>=', now()->subSeconds(4))
    ->get();

    $sessionLeft = max(0, 450 - now()->diffInSeconds($room->game_started_at));

    if ($sessionLeft === 0 && $room->status === 'playing') {
    DB::table('multiplayer_rooms')->where('id', $room->id)->update([
        'status' => 'finished',
        'question_order' => null,
        'current_question_index' => 0
    ]);

    return response()->json([
        'room_status' => 'finished',
        'session_left' => 0,
        'players' => $players,
        'stickers' => [],
    ]);
}

$revealed = null;
$turnLeft = $room->turn_locked
    ? 0
    : max(0, 60 - now()->diffInSeconds($room->turn_started_at));

if ($room->revealed_answer && $room->reveal_until) {

    if (now()->lessThan($room->reveal_until)) {

        $revealed = [
            'answer' => $room->revealed_answer,
        ];

    } else {

        // ⏭️ LANJUT SOAL SETELAH REVEAL
        $questionOrder = json_decode($room->question_order, true) ?? [];
        $nextIndex = $room->current_question_index + 1;

        if ($nextIndex >= count($questionOrder)) {
            DB::table('multiplayer_rooms')
                ->where('id', $room->id)
                ->update([
                    'status'          => 'finished',
                    'revealed_answer' => null,
                    'reveal_until'    => null,
                ]);
        } else {
            DB::table('multiplayer_rooms')
                ->where('id', $room->id)
                ->update([
                    'current_question_index' => $nextIndex,
                    'revealed_answer'        => null,
                    'reveal_until'           => null,
                    'turn_started_at'        => now(),
                    'turn_locked'            => false,
                ]);
        }
        $room = DB::table('multiplayer_rooms')->where('id', $room->id)->first();
        $questionOrder = json_decode($room->question_order, true) ?? [];
        $qid = $questionOrder[$room->current_question_index] ?? null;
        $question = $qid ? Question::find($qid) : null;

    }
}


    return response()->json([
    'room_status'            => $room->status,
    'session_left'           => $sessionLeft,
    'reveal' => $revealed,

    'players'                => $players,
    'current_turn_player_id' => $room->current_turn_player_id,
    'my_player_id'           => $playerId,
    'turn_left'              => $turnLeft,

    'question' => $question ? [
        'image'         => '/' . ltrim($question->image_path, '/'),
        'answer_length' => (int) $question->answer_slots,
    ] : null,

    'stickers' => $stickers,
]);

}


    /* =========================================================
     * ANSWER
     * =======================================================*/
    public function submitAnswer(Request $request)
{
    $request->validate([
        'room_code' => 'required',
        'answer'    => 'required|string',
    ]);

    $playerId = session('multiplayer_player_id');
    if (!$playerId) {
        return response()->json(['error' => 'Invalid player'], 403);
    }

    DB::beginTransaction();

    $room = DB::table('multiplayer_rooms')
        ->where('room_code', $request->room_code)
        ->lockForUpdate()
        ->first();

    if (!$room || $room->status === 'finished') {
        DB::rollBack();
        return response()->json(['error' => 'Game finished'], 403);
    }

    // ⛔ BUKAN GILIRAN / TURN TERKUNCI
    if ($room->current_turn_player_id !== $playerId || $room->turn_locked) {
        DB::rollBack();
        return response()->json(['error' => 'Not your turn'], 403);
    }

    $questionOrder = json_decode($room->question_order, true) ?? [];
    $currentIndex  = (int) $room->current_question_index;

    $qid = $questionOrder[$currentIndex] ?? null;
    $question = $qid ? Question::find($qid) : null;

    if (!$question) {
        DB::rollBack();
        return response()->json(['error' => 'Question not found'], 404);
    }

    $correct =
        $this->normalize($request->answer) ===
        $this->normalize($question->answer_text);

    if ($correct) {

        $cleanAnswer = strtoupper(
        preg_replace('/[^A-Z0-9]/i', '', $question->answer_text)
        );

        // 🔒 LOCK TURN + SET REVEAL
        DB::table('multiplayer_rooms')
            ->where('id', $room->id)
            ->update([
                'turn_locked'     => true,
                'revealed_answer' => $cleanAnswer,
                'reveal_until'    => now()->addSeconds(3),
            ]);

        DB::table('multiplayer_room_players')
            ->where('id', $playerId)
            ->increment('score');

        DB::commit();

        return response()->json([
            'correct'  => true,
            'finished' => false,
        ]);
    }

    // ❌ SALAH → PINDAH GILIRAN (TANPA REVEAL)
    $currentOrder = DB::table('multiplayer_room_players')
        ->where('id', $playerId)
        ->value('turn_order');

    $nextPlayer = DB::table('multiplayer_room_players')
        ->where('room_id', $room->id)
        ->where('turn_order', '>', $currentOrder)
        ->orderBy('turn_order')
        ->first()
        ?? DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->orderBy('turn_order')
            ->first();

    DB::table('multiplayer_rooms')
        ->where('id', $room->id)
        ->update([
            'current_turn_player_id' => $nextPlayer->id,
            'turn_started_at'        => now(),
            'turn_locked'            => false,
        ]);

    DB::commit();

    return response()->json([
        'correct'  => false,
        'finished' => false,
    ]);
}

    /* =========================================================
     * STICKER
     * =======================================================*/
    public function sendSticker(Request $request)
    {
        $request->validate([
            'room_code' => 'required',
            'sticker_id'=> 'required|integer',
            'emoji'      => 'required|string|max:10',
        ]);

        $playerId = session('multiplayer_player_id');
        if (!$playerId) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $room = DB::table('multiplayer_rooms')
            ->where('room_code', $request->room_code)
            ->first();

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        DB::table('multiplayer_stickers')->insert([
            'room_id'    => $room->id,
            'player_id'  => $playerId,
            'emoji'      => $request->emoji,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
