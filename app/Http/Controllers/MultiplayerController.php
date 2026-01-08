<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Question;

class MultiplayerController extends Controller
{
    private function normalize(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $value));
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
                'players'     => $players,
                'is_my_turn'  => false,
            ]);
        }

        $turnLeft = max(0, 30 - now()->diffInSeconds($room->turn_started_at));
        $sessionLeft = max(0, 350 - now()->diffInSeconds($room->game_started_at));

        $question = Question::where('image_path', 'like', 'images/%')
            ->orderBy('id')
            ->skip($room->current_question_index)
            ->first();

        $stickers = DB::table('multiplayer_stickers')
            ->where('room_id', $room->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'room_status'            => 'playing',
            'players'                => $players,
            'current_turn_player_id' => $room->current_turn_player_id,
            'is_my_turn'             => $room->current_turn_player_id === $playerId,
            'turn_left'              => $turnLeft,
            'session_left'           => $sessionLeft,
            'question'               => $question ? [
                'image'         => '/' . ltrim($question->image_path, '/'),
                'answer_length' => (int) $question->answer_slots,
            ] : null,
            'stickers' => $stickers->map(fn ($s) => [
                'id'        => $s->id,
                'player_id' => $s->player_id,
                'sticker'   => $s->emoji,
            ]),
            'last_validation' => $room->last_validation
                ? json_decode($room->last_validation, true)
                : null,
        ]);
    }

    /* =========================
     * ANSWER
     * ========================= */
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

        if (!$room || $room->current_turn_player_id !== $playerId || $room->turn_locked) {
            DB::rollBack();
            return response()->json(['error' => 'Not your turn'], 403);
        }

        DB::table('multiplayer_rooms')->where('id', $room->id)
            ->update(['turn_locked' => true]);

        $question = Question::where('image_path', 'like', 'images/%')
            ->orderBy('id')
            ->skip($room->current_question_index)
            ->first();

        $correct = $this->normalize($request->answer) ===
                   $this->normalize($question->answer_text);

        if ($correct) {
            DB::table('multiplayer_room_players')
                ->where('id', $playerId)
                ->increment('score');

            DB::table('multiplayer_rooms')
                ->where('id', $room->id)
                ->increment('current_question_index');
        }

        // NEXT PLAYER
        $currentOrder = DB::table('multiplayer_room_players')
            ->where('id', $playerId)
            ->value('turn_order');

        $next = DB::table('multiplayer_room_players')
            ->where('room_id', $room->id)
            ->where('turn_order', '>', $currentOrder)
            ->orderBy('turn_order')
            ->first()
            ?? DB::table('multiplayer_room_players')
                ->where('room_id', $room->id)
                ->orderBy('turn_order')
                ->first();

        DB::table('multiplayer_rooms')->where('id', $room->id)->update([
            'current_turn_player_id' => $next->id,
            'turn_started_at'        => now(),
            'turn_locked'            => false,
            'last_validation'        => json_encode(['correct' => $correct]),
        ]);

        DB::commit();

        return response()->json(['correct' => $correct]);
    }

    /* =========================
     * STICKER
     * ========================= */
    public function sendSticker(Request $request)
    {
        $request->validate([
            'room_code' => 'required',
            'sticker'   => 'required|string|max:10',
        ]);

        $playerId = session('multiplayer_player_id');
        if (!$playerId) {
            return response()->json(['error' => 'Invalid player'], 403);
        }

        $room = DB::table('multiplayer_rooms')
            ->where('room_code', $request->room_code)
            ->first();

        DB::table('multiplayer_stickers')->insert([
            'room_id'   => $room->id,
            'player_id' => $playerId,
            'emoji'     => $request->sticker,
            'created_at'=> now(),
        ]);

        return response()->json(['success' => true]);
    }
}
