<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Question;

class MultiplayerController extends Controller
{
    /* =========================
     * UTIL
     * ========================= */
    private function normalize(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $value));
    }

    /* =========================
     * CREATE ROOM
     * ========================= */
    public function createRoom(Request $request)
    {
        $request->validate([
            'player_name' => 'required|string|max:30',
            'max_players' => 'required|integer|min:2|max:4',
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

    /* =========================
     * JOIN ROOM
     * ========================= */
    public function joinRoom(Request $request)
    {
        $request->validate([
            'player_name' => 'required|string|max:30',
            'room_code'   => 'required|string',
        ]);

        $room = DB::table('multiplayer_rooms')
            ->where('room_code', $request->room_code)
            ->first();

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $playerId = DB::table('multiplayer_room_players')->insertGetId([
            'room_id' => $room->id,
            'player_name' => $request->player_name,
            'color' => 'red',
            'score' => 0,
            'joined_at' => now(),
        ]);

        session(['multiplayer_player_id' => $playerId]);

        return response()->json(['success' => true]);
    }

    /* =========================
     * GAME STATE (POLLING)
     * ========================= */
    public function gameState(string $code)
    {
        $room = DB::table('multiplayer_rooms')->where('room_code', $code)->first();
        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $players = DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->orderBy('turn_order')
            ->get();

        $playerId = session('multiplayer_player_id');

        if ($room->status !== 'playing') {
            return response()->json([
                'room_status' => $room->status,
                'players' => $players,
                'is_my_turn' => false,
            ]);
        }

        $turnLeft = max(0, 30 - now()->diffInSeconds($room->turn_started_at));
        $sessionLeft = max(0, 350 - now()->diffInSeconds($room->game_started_at));

        $question = Question::where('image_path', 'like', 'images/%')
            ->orderBy('id')
            ->skip($room->current_question_index)
            ->first();

        return response()->json([
            'room_status' => 'playing',
            'players' => $players,
            'current_turn_player_id' => $room->current_turn_player_id,
            'is_my_turn' => $room->current_turn_player_id === $playerId,
            'turn_left' => $turnLeft,
            'session_left' => $sessionLeft,
            'question' => $question ? [
                'image' => '/' . ltrim($question->image_path, '/'),
                'answer_length' => (int) $question->answer_slots,
            ] : null,
        ]);
    }

    /* =========================
     * ANSWER
     * ========================= */
    public function submitAnswer(Request $request)
    {
        $request->validate([
            'room_code' => 'required',
            'answer' => 'required|string',
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

        if (!$room || $room->current_turn_player_id !== $playerId) {
            DB::rollBack();
            return response()->json(['error' => 'Not your turn'], 403);
        }

        DB::commit();
        return response()->json(['success' => true]);
    }

    /* =========================
     * STICKER
     * ========================= */
    public function sendSticker(Request $request)
    {
        $request->validate([
            'room_code' => 'required',
            'sticker' => 'required|string|max:10',
        ]);

        $playerId = session('multiplayer_player_id');
        if (!$playerId) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $room = DB::table('multiplayer_rooms')
            ->where('room_code', $request->room_code)
            ->first();

        DB::table('multiplayer_stickers')->insert([
            'room_id' => $room->id,
            'player_id' => $playerId,
            'emoji' => $request->sticker,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
