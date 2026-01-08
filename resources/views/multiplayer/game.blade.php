@extends('layouts.app')

@section('content')
<div
  x-data="multiplayerGame('{{ $roomCode }}')"
  x-init="init()"
  x-cloak
  class="relative max-w-6xl mx-auto p-4"
>

  <!-- PLAYER POSITIONS -->
  <div class="relative h-[520px]">

    <template x-for="(p, i) in players" :key="p.id">
      <div
        class="player-card relative"
        :class="[
          p.id === currentTurnId ? 'active-turn' : '',
          positionClass(i),
          colorClass(p.color)
        ]"
      >
        <div class="flex justify-between items-center">
          <div>
            <div class="font-bold text-sm" x-text="p.player_name"></div>
            <div class="text-xs">Skor: <span x-text="p.score"></span></div>
          </div>

          <!-- STICKER DI DALAM CARD -->
          <template x-if="playerSticker(p.id)">
            <div class="w-8 h-8 rounded-full bg-gray-100
                        flex items-center justify-center text-lg">
              <span x-text="playerSticker(p.id)"></span>
            </div>
          </template>
        </div>
      </div>
    </template>

    <!-- CENTER GAME -->
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
      <div class="bg-white rounded-lg shadow p-4 w-full max-w-md pointer-events-auto">

        <!-- TIMERS -->
        <div class="flex justify-between text-xs font-semibold mb-2">
          <div>⏳ Game: <span x-text="sessionLeft"></span>s</div>
          <div :class="turnLeft <= 5 ? 'text-red-600 animate-pulse' : ''">
            🎯 Giliran: <span x-text="turnLeft"></span>s
          </div>
        </div>

        <!-- IMAGE -->
        <div class="flex justify-center mb-4" x-show="question">
          <img :src="imageSrc" class="max-h-[240px] object-contain rounded">
        </div>

        <!-- ANSWER SLOTS -->
        <div class="flex justify-center gap-1 mb-3">
          <template x-for="i in answerSlots" :key="i">
            <div class="w-9 h-9 border rounded flex items-center justify-center bg-gray-100 font-bold">
              ?
            </div>
          </template>
        </div>

        <!-- INPUT -->
        <div class="flex gap-2">
          <input
            x-model="answer"
            :disabled="!isMyTurn || submitting"
            class="flex-1 border rounded px-3 py-2"
            placeholder="Jawaban..."
          >
          <button
            @click="submit"
            :disabled="!isMyTurn || submitting"
            class="border border-indigo-600 text-indigo-600
                   font-semibold rounded px-4 py-2
                   hover:bg-indigo-50 transition">
            Kirim
          </button>
        </div>

        <!-- FEEDBACK BENAR / SALAH -->
        <template x-if="answerResult">
          <div
            class="mt-3 text-center font-bold text-sm animate-pulse"
            :class="answerResult === 'correct'
              ? 'text-green-600'
              : 'text-red-600'"
            x-text="answerResult === 'correct'
              ? '✅ Jawaban Benar!'
              : '❌ Jawaban Salah!'">
          </div>
        </template>

      </div>
    </div>

    <!-- STICKER BAR (AREA MERAH – BAWAH GAME) -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2
                bg-white rounded-xl shadow px-4 py-2
                flex gap-3 z-10">

      <template x-for="s in stickers" :key="s.id">
        <button
          @click="sendSticker(s)"
          :disabled="stickerCooldown"
          class="text-2xl transition transform
                 hover:scale-125 active:scale-95
                 disabled:opacity-40">
          <span x-text="s.emoji"></span>
        </button>
      </template>

    </div>

  </div>
</div>

<!-- GAME FINISHED POPUP -->
<template x-if="roomStatus === 'finished'">
  <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-80 text-center">
      <div class="text-3xl mb-2">🏁</div>
      <h2 class="font-bold text-lg mb-3">Game Berakhir</h2>

      <div class="text-sm space-y-1 mb-4">
        <template x-for="p in players" :key="p.id">
          <div class="flex justify-between">
            <span x-text="p.player_name"></span>
            <span x-text="p.score"></span>
          </div>
        </template>
      </div>

      <a href="/dashboard"
         class="block bg-indigo-600 text-white rounded py-2">
        Kembali ke Dashboard
      </a>
    </div>
  </div>
</template>
<script>
function multiplayerGame(roomCode){
  return {
    roomCode,

    roomStatus: null,

    players: [],
    currentTurnId: null,
    myPlayerId: null,

    question: null,
    sessionLeft: 0,
    turnLeft: 0,

    answer: '',
    submitting: false,
    answerResult: null,

    stickers: [
      { id: 1, emoji: '👍' },
      { id: 2, emoji: '😂' },
      { id: 3, emoji: '🔥' },
      { id: 4, emoji: '😱' },
      { id: 5, emoji: '👏' },
    ],

    stickersLive: [],
    stickerCooldown: false,

    init(){
      this.fetchState();
      setInterval(() => {
        this.fetchState();
      }, 3000);
    },

    fetchState(){
      fetch(`/multiplayer/game-state/${this.roomCode}`)
        .then(r => r.json())
        .then(d => {
          this.players = d.players ?? [];
          this.currentTurnId = d.current_turn_player_id;
          this.myPlayerId = d.my_player_id;
          this.question = d.question;
          this.turnLeft = d.turn_left ?? 0;
          this.sessionLeft = d.session_left ?? 0;
          this.roomStatus = d.room_status;
          this.stickersLive = d.stickers ?? [];
        });
    },

    submit(){
      if (!this.isMyTurn || !this.answer.trim()) return;

      this.submitting = true;
      this.answerResult = null;

      fetch('/multiplayer/answer', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          room_code: this.roomCode,
          answer: this.answer
        })
      })
      .then(r => r.json())
      .then(res => {
        this.answerResult = res.correct ? 'correct' : 'wrong';
        this.answer = '';
        setTimeout(() => this.answerResult = null, 2000);
        this.fetchState();
      })
      .finally(() => this.submitting = false);
    },

    sendSticker(sticker){
      if (this.stickerCooldown) return;

      this.stickerCooldown = true;

      fetch('/multiplayer/sticker', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          room_code: this.roomCode,
          sticker_id: sticker.id,
          emoji: sticker.emoji
        })
      }).then(() => {
        this.fetchState();
      });

      setTimeout(() => this.stickerCooldown = false, 5000);
    },

    playerSticker(pid){
      const s = this.stickersLive.find(x => x.player_id === pid);
      return s ? s.emoji : null;
    },

    get isMyTurn(){
      return this.myPlayerId === this.currentTurnId;
    },

    get answerSlots(){
      return this.question ? this.question.answer_length : 0;
    },

    get imageSrc(){
      return this.question?.image ?? '';
    },

    positionClass(i){
      return ['top-left','top-right','bottom-left','bottom-right'][i] || '';
    },

    colorClass(c){
      return {
        blue:'border-blue-400 bg-blue-50',
        red:'border-red-400 bg-red-50',
        orange:'border-orange-400 bg-orange-50',
        green:'border-green-500 bg-green-50'
      }[c];
    }
  }
}
</script>
@endsection