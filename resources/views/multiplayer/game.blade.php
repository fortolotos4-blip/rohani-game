@extends('layouts.app')

@section('content')
<div
  x-data="multiplayerGame('{{ $roomCode }}')"
  x-init="init()"
  x-cloak
  class="relative max-w-6xl mx-auto p-4"
>

  <!-- STICKER CHAT -->
  <div class="fixed bottom-20 right-4 space-y-2 w-48 pointer-events-none">
    <template x-for="s in stickersLive" :key="s.id">
      <div class="bg-white rounded-xl shadow px-3 py-2 text-lg flex items-center gap-2">
        <span class="text-xs font-semibold text-gray-500"
          x-text="playerName(s.player_id)">
        </span>
        <span x-text="s.sticker"></span>
      </div>
    </template>
  </div>

  <!-- PLAYER POSITIONS -->
  <div class="relative h-[520px]">

    <template x-for="(p, i) in players" :key="p.id">
      <div
        class="player-card"
        :class="[
          p.id === currentTurnId ? 'active-turn' : '',
          positionClass(i),
          colorClass(p.color)
        ]"
      >
        <div class="font-bold text-sm" x-text="p.player_name"></div>
        <div class="text-xs">Skor: <span x-text="p.score"></span></div>
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
            <div class="w-9 h-9 border rounded flex items-center justify-center font-bold bg-gray-100">
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
            class="px-4 py-2 bg-indigo-600 text-white rounded disabled:opacity-50"
          >
            Kirim
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- STICKERS -->
  <div class="mt-4 text-center">
    <div class="flex justify-center gap-2">
      <template x-for="s in stickers" :key="s">
        <button
          @click="sendSticker(s)"
          :disabled="stickerCooldown"
          class="px-3 py-1 border rounded bg-gray-100 disabled:opacity-40"
        >
          <span x-text="s"></span>
        </button>
      </template>
    </div>
  </div>

</div>

<script>
function multiplayerGame(roomCode){
  return {
    roomCode,

    players: [],
    currentTurnId: null,
    question: null,

    sessionLeft: 0,
    turnLeft: 0,

    answer: '',
    submitting: false,

    stickers: ['👍','😂','🔥','😱','👏'],
    stickersLive: [],
    stickerCooldown: false,

    myPlayerId: null,


    init(){
      this.fetchState();
      setInterval(() => {
      if (!this.submitting) this.fetchState();
    }, 2000);
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

            // STICKER
            this.stickersLive = d.stickers ?? [];
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

      sendSticker(s){
    if (this.stickerCooldown) return;

    this.stickerCooldown = true;

    fetch('/multiplayer/sticker', {
      method: 'POST',
      credentials: 'same-origin', // 🔥 INI WAJIB
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        room_code: this.roomCode,
        sticker: s
      })
    })
    .then(r => {
      if (!r.ok) throw new Error('Sticker failed');
      return r.json();
    })
    .then(() => {
      this.fetchState(); // tampilkan bubble
    })
    .catch(err => {
      console.warn(err.message);
    });

    setTimeout(() => this.stickerCooldown = false, 5000);
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
</style>
@endsection
