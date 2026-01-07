@extends('layouts.app')

@section('content')
<div
  x-data="multiplayerGame('{{ $roomCode }}')"
  x-init="init()"
  class="relative max-w-6xl mx-auto p-4 overflow-hidden"
>

<div class="fixed bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
  <template x-for="s in stickersLive" :key="s.id">
    <div class="px-3 py-1 bg-white rounded shadow text-lg">
      <span x-text="s.sticker"></span>
    </div>
  </template>
</div>

  <!-- GAME OVER -->
  <div
    x-show="gameOver"
    x-cloak
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-lg p-6 w-80 text-center">
      <h2 class="text-xl font-bold mb-4">🏁 Game Selesai</h2>

      <template x-for="p in players" :key="p.id">
        <div class="flex justify-between text-sm mb-1">
          <span x-text="p.player_name"></span>
          <span class="font-bold" x-text="p.score"></span>
        </div>
      </template>

      <a
        href="{{ route('guess.menu') }}"
        class="mt-4 block bg-indigo-600 text-white rounded px-4 py-2"
      >
        Kembali ke Menu
      </a>
    </div>
  </div>

  <!-- PLAYER POSITIONS -->
  <div class="relative h-[520px]">

    <!-- PLAYER CARD -->
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
          <div :class="turnLeft<=5 ? 'text-red-600 animate-pulse':''">
            🎯 Giliran: <span x-text="turnLeft"></span>s
          </div>
        </div>

        <!-- IMAGE -->
        <div class="flex justify-center mb-4">
          <img
            :src="imageSrc"
            class="max-h-[240px] object-contain rounded"
          >
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
        <div class="flex gap-2 mb-2">
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

        <!-- VALIDATION -->
        <div
  x-show="lastValidation"
  x-transition
  class="text-center text-sm font-semibold"
  :class="[
    lastValidation.correct ? 'text-green-600' : 'text-red-600 shake',
    lastValidation.player_id === currentTurnId ? 'ring-2 ring-indigo-400' : ''
  ]"
>
          <span x-text="validationText"></span>
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
    stickersLive: [],
    sessionLeft: 0,
    turnLeft: 30,

    lastStickerId: 0,

    answer: '',
    submitting: false,
    lastValidation: null,

    pollId: null,

    stickers: ['👍','😂','🔥','😱','👏'],
    stickerCooldown: false,

    answerSlots: 6,
    gameOver: false,

    init(){
  if(!localStorage.getItem('mp_player_id')){
    localStorage.setItem(
      'mp_player_id',
      {{ session('multiplayer_player_id') ?? 'null' }}
    );
  }
  this.fetchState();
  this.pollId = setInterval(this.fetchState, 2000);
},


    fetchState(){
      if (this.gameOver) return;
      fetch(`/api/multiplayer/game-state/${this.roomCode}`)
        .then(r=>r.json())
        .then(d=>{
          this.players = d.players;
          this.sessionLeft = d.session_left;
          this.currentTurnId = d.current_turn_player_id;
          this.turnLeft = d.turn_left;
          const incoming = d.stickers ?? [];

          // ambil hanya sticker baru
          const fresh = incoming.filter(s => s.id > this.lastStickerId);

          fresh.forEach(s => {
            this.stickersLive.push(s);

            // auto remove setelah 3 detik
            setTimeout(() => {
              this.stickersLive = this.stickersLive.filter(x => x.id !== s.id);
            }, 3000);
          });

          if (fresh.length) {
            this.lastStickerId = fresh.at(-1).id;
          }

          if(d.last_validation){
            this.lastValidation = d.last_validation;
            setTimeout(()=>this.lastValidation=null,1500);
          }

          if(this.sessionLeft <= 0){
            this.gameOver = true;
            clearInterval(this.pollId);

          }
          if(d.room_status === 'finished'){
          this.gameOver = true;
        }

        });
    },

    submit(){
      if(this.submitting) return;
      this.submitting = true;

      fetch('/api/multiplayer/answer',{
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({
          room_code:this.roomCode,
          answer:this.answer
        })
      }).finally(()=>{
        this.answer='';
        this.submitting=false;
      });
    },

    sendSticker(s){
      if(this.stickerCooldown) return;
      this.stickerCooldown = true;

      fetch('/api/multiplayer/sticker',{
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({room_code:this.roomCode,sticker:s})
      });

      setTimeout(()=>this.stickerCooldown=false,20000);
    },

    get isMyTurn(){
  const sessionId = {{ session('multiplayer_player_id') ?? 'null' }};
  const me = sessionId !== null
    ? sessionId
    : parseInt(localStorage.getItem('mp_player_id'));

  return this.currentTurnId === me;
},

    get validationText(){
      return this.lastValidation?.correct ? 'Jawaban BENAR!' : 'Jawaban SALAH!';
    },

    get imageSrc(){
      return '/images/placeholder.png';
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

@keyframes shake{
  0%,100%{transform:translateX(0)}
  25%{transform:translateX(-5px)}
  75%{transform:translateX(5px)}
}
.shake{animation:shake .3s}
</style>
@endsection
