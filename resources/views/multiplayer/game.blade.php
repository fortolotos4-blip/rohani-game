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

          <!-- STICKER DI CARD -->
          <template x-if="playerSticker(p.id)">
            <div
              :class="[
                'inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-lg',
                'sticker-animate',
                isStickerExpiring(playerStickerObj(p.id)) ? 'sticker-fade' : ''
              ]"
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
        <template x-for="(n, i) in answerSlots" :key="i">
      <div
        class="w-9 h-9 border rounded flex items-center justify-center font-bold transition"
        :class="revealedAnswer
          ? 'bg-green-100 border-green-500 text-green-700'
          : 'bg-gray-100'"
      >
        <span
          x-text="
            revealedAnswer
              ? revealedAnswer[i] ?? ''
              : '?'
          "
        ></span>
      </div>
    </template>
      </div>

      <!-- INPUT -->
      <div class="flex gap-2 flex-col sm:flex-row">
        <input
          x-model="answer"
          :disabled="!isMyTurn || submitting || roomStatus === 'finished' || revealedAnswer"
          :class="{
            'border-green-500 ring-2 ring-green-400': answerState === 'correct',
            'border-red-500 ring-2 ring-red-400': answerState === 'wrong'
          }"
          class="flex-1 border rounded px-3 py-2 transition"
          placeholder="Jawaban..."
        >
        <button
          @click="submit"
          :disabled="!isMyTurn || submitting || revealedAnswer"
          class="w-full sm:w-auto border border-indigo-600
                 text-indigo-600 font-semibold rounded px-4 py-2">
          Kirim
        </button>
      </div>

    </div>
  </div>

  <!-- ✅ STICKER BAR (MASIH DALAM x-data) -->
  <div
    class="fixed bottom-6 left-1/2 -translate-x-1/2
           bg-white rounded-xl shadow-lg
           px-4 py-2 flex gap-3 z-50"
  >
    <template x-for="s in stickers" :key="s.id">
      <button
        @click="sendSticker(s)"
        :disabled="stickerCooldown || roomStatus === 'finished'"
        class="text-2xl transition transform
               hover:scale-125 active:scale-95
               disabled:opacity-40"
      >
        <span x-text="s.emoji"></span>
      </button>
    </template>
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

</div> <!-- ✅ x-data DITUTUP PALING AKHIR -->

<script>
function multiplayerGame(roomCode){
  return {
    roomCode,

    revealedAnswer: null,

    roomStatus: null,

    answerState: null, // 'correct' | 'wrong'

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
      this._poller = setInterval(() => {
      this.fetchState();
    }, 3000);
    },

    fetchState(){
  fetch(`/multiplayer/game-state/${this.roomCode}`, {
    credentials: 'same-origin'
  })
  .then(r => {
    if (!r.ok) throw new Error('Network error');
    return r.json();
  
  })
  .then(d => {
    this.players = d.players ?? [];
    this.currentTurnId = d.current_turn_player_id;
    this.myPlayerId = d.my_player_id;
    this.question = d.question;
    this.revealedAnswer = d.reveal?.answer ?? null;
    this.turnLeft = d.turn_left ?? 0;
    this.sessionLeft = d.session_left ?? 0;
    this.roomStatus = d.room_status;
    this.stickersLive = d.stickers ?? [];

    // ⛔ hentikan polling SETELAH status finished diterima
    if (this.roomStatus === 'finished') {
      clearInterval(this._poller);
      this._poller = null;
    }
  })
  .catch(() => console.warn('Polling failed'));
},

    submit(){
    if (!this.isMyTurn || this.submitting || !this.answer.trim()) return;

    this.submitting = true;

    fetch('/multiplayer/answer', {
    method: 'POST',
    credentials: 'same-origin', // ✅ WAJIB
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
      this.answerState = res.correct ? 'correct' : 'wrong';
      setTimeout(() => {
      this.answerState = null;
      this.answer = '';
      this.fetchState();
    }, 800); // skor & turn update DI SINI
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
isStickerExpiring(sticker) {
  const created = new Date(sticker.created_at).getTime();
  return Date.now() - created > 3000; // 3 detik → mulai fade
},

playerStickerObj(pid) {
  return this.stickersLive.find(x => x.player_id === pid);
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
@keyframes sticker-pop {
  0% {
    transform: scale(0.2);
    opacity: 0;
  }
  60% {
    transform: scale(1.3);
    opacity: 1;
  }
  80% {
    transform: scale(0.95);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes sticker-fade {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

.sticker-animate {
  animation: sticker-pop 0.35s ease-out;
}

.sticker-fade {
  animation: sticker-fade 0.6s ease-in forwards;
}
</style>
@endsection
