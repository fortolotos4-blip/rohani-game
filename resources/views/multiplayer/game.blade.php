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

      <!-- ISI CARD -->
      <div class="flex justify-between items-center">

        <!-- NAMA & SKOR -->
        <div>
          <div class="font-bold text-sm" x-text="p.player_name"></div>
          <div class="text-xs">
            Skor: <span x-text="p.score"></span>
          </div>
        </div>

        <!-- STICKER (DI DALAM KOTAK MERAH) -->
        <template x-if="playerSticker(p.id)">
          <div
            class="inline-flex items-center justify-center
                   w-8 h-8 rounded-full bg-gray-100 text-lg"
          >
            <span x-text="playerSticker(p.id)"></span>
          </div>
        </template>

      </div>

    </div>
  </template>

</div>


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
            <div class="w-9 h-9 border rounded flex items-center justify-center font-bold bg-gray-100">
              ?
            </div>
          </template>
        </div>

        <!-- INPUT -->
        <div class="flex gap-2 flex-col sm:flex-row">
          <input
            x-model="answer"
            :disabled="!isMyTurn || submitting"
            class="flex-1 border rounded px-3 py-2"
            placeholder="Jawaban..."
          >
          <button
            @click="submit"
            :disabled="!isMyTurn || submitting"
            class="w-full sm:w-auto border border-indigo-600
         text-indigo-600 font-semibold rounded px-4 py-2">
            Kirim
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- STICKERS -->
  <div class="mt-4 text-center">
    <div class="flex justify-center gap-2">
      <template x-for="s in stickers" :key="s.id">
      <button
        @click="sendSticker(s)"
        :disabled="stickerCooldown"
        class="px-3 py-1 border rounded bg-gray-100 disabled:opacity-40"
      >
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
      <h2 class="font-bold text-lg mb-2">Game Berakhir</h2>

      <div class="text-sm mb-4">
        <template x-for="p in players" :key="p.id">
          <div class="flex justify-between">
            <span x-text="p.player_name"></span>
            <span x-text="p.score"></span>
          </div>
        </template>
      </div>

      <a href="/dashboard"
         class="block mt-3 bg-indigo-600 text-white rounded py-2">
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
    question: null,

    sessionLeft: 0,
    turnLeft: 0,

    answer: '',
    submitting: false,

    stickers: [
  { id: 1, emoji: '👍' },
  { id: 2, emoji: '😂' },
  { id: 3, emoji: '🔥' },
  { id: 4, emoji: '😱' },
  { id: 5, emoji: '👏' },
],

    stickersLive: [],
    stickerCooldown: false,

    myPlayerId: null,


    init(){
      this.fetchState();
      setInterval(() => {
      if (!this.submitting) this.fetchState();
    }, 3000);
    },

    fetchState(){
      fetch(`/multiplayer/game-state/${this.roomCode}`)
      .then(r => {
        if (!r.ok) throw new Error('Network error');
        return r.json();
      })
      .then(d => {
        this.players = d.players ?? [];
            this.currentTurnId = d.current_turn_player_id;
            this.myPlayerId = d.my_player_id;

            this.question = d.question;
            this.turnLeft = d.turn_left ?? 0;
            this.sessionLeft = d.session_left ?? 0;

            this.roomStatus = d.room_status;

            // STICKER
            this.stickersLive = d.stickers ?? [];

            // auto-clear setelah 2 detik (client only)
            clearTimeout(this._stickerTimer);
            this._stickerTimer = setTimeout(() => {
              this.stickersLive = [];
            }, 2000);

      })
      .catch(() => {
        console.warn('Polling failed');
      });
    },

    submit(){
    if (!this.isMyTurn || this.submitting || !this.answer.trim()) return;

    this.submitting = true;

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
    .then(r => {
      if (!r.ok) throw new Error('Submit failed');
      return r.json();
    })
    .then(res => {
      if (res.correct) {
        console.log('Jawaban benar');
      } else {
        console.log('Jawaban salah');
      }
      this.answer = '';
      this.fetchState(); // skor & turn update DI SINI
    })
    .catch(err => {
      console.warn(err.message);
    })
    .finally(() => this.submitting = false);
  },

      sendSticker(sticker){
  if (this.stickerCooldown) return;

  this.stickerCooldown = true;

  fetch('/multiplayer/sticker', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({
      room_code: this.roomCode,
      sticker_id: sticker.id,     // 🔥 IDENTITAS
      emoji: sticker.emoji        // 🔥 DATA
    })
  })
  .then(r => {
    if (!r.ok) throw new Error('Sticker failed');
    return r.json();
  })
  .then(() => {
    this.fetchState();
  })
  .catch(err => console.warn(err.message));

  setTimeout(() => this.stickerCooldown = false, 5000);
},
playerSticker(pid) {
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

    playerName(pid){
      const p = this.players.find(x => x.id === pid);
      return p ? p.player_name : '';
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

<style>
.player-card{
  position:absolute;
  width:140px;
  padding:8px;
  border-radius:10px;
  border:2px solid;
  font-size:12px;
}
.top-left{top:0;left:0}
.top-right{top:0;right:0}
.bottom-left{bottom:0;left:0}
.bottom-right{bottom:0;right:0}
.active-turn{
  box-shadow:0 0 0 3px rgba(99,102,241,.4);
}
.player-sticker{
  position:absolute;
  top:-18px;
  left:50%;
  transform:translateX(-50%);
  font-size:20px;
  line-height:1;
  pointer-events:none;
}

</style>
@endsection
