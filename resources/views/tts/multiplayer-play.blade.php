@extends('layouts.app')

@section('content')
<div x-data="ttsMultiplayer()" x-init="init()"
     class="max-w-6xl mx-auto p-4 sm:p-6 bg-white rounded shadow">

<!-- ================= ROOM INFO ================= -->
<div class="mb-4">

  <!-- MODE WAITING / RPS (BESAR) -->
  <template x-if="status !== 'playing'">
    <div class="bg-gray-50 border rounded-lg p-4 text-sm text-gray-700 text-center space-y-2">

      <div class="flex justify-center items-center gap-2 font-semibold">
        👤 <span x-text="player1"></span>
        <span class="mx-1">VS</span>
        👤 <span x-text="player2 || 'Menunggu…'"></span>
      </div>

      <div>
        Status: <b x-text="status"></b>
      </div>

      <div>
        <p class="text-xs text-gray-500">Bagikan kode ini untuk bermain</p>
        <div
          class="inline-block mt-1 px-6 py-2 text-lg font-bold tracking-widest
                 bg-white border rounded shadow-sm select-all">
          {{ $room->room_code }}
        </div>
      </div>

    </div>
  </template>

 <!-- MODE PLAYING (MINIMAL, SEIMBANG) -->
<template x-if="status === 'playing'">
  <div
    class="flex justify-between items-center
           bg-white border rounded px-3 py-2 text-xs text-gray-600">

    <!-- LEFT : ROOM -->
    <div
      class="relative group cursor-pointer font-semibold
             px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700"
      @click="navigator.clipboard.writeText('{{ $room->room_code }}')">

      Room: {{ $room->room_code }}

      <!-- TOOLTIP -->
      <div
        class="absolute left-0 top-full mt-2 w-max
               bg-black text-white text-[11px]
               px-3 py-1 rounded opacity-0
               group-hover:opacity-100 transition pointer-events-none">
        Klik untuk menyalin kode room
      </div>
    </div>

    <!-- RIGHT : STATUS -->
    <div class="flex items-center gap-1 font-semibold text-green-600">
      <span class="text-xs">●</span>
      Playing
    </div>

  </div>
</template>

</div>

  <!-- ================= RPS POPUP ================= -->
  <div x-show="status === 'rps'"
       class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded text-center w-80">
      <h2 class="text-xl font-bold mb-3">✊✋✌️ Gunting Batu Kertas</h2>
      <p class="text-sm mb-4">Menentukan giliran pertama</p>

      <div class="flex justify-center gap-4">
        <button @click="sendRps('rock')" class="px-4 py-2 bg-gray-200 rounded">✊</button>
        <button @click="sendRps('paper')" class="px-4 py-2 bg-gray-200 rounded">✋</button>
        <button @click="sendRps('scissors')" class="px-4 py-2 bg-gray-200 rounded">✌️</button>
      </div>

      <p x-show="rpsWaiting" class="text-xs text-gray-500 mt-3">
        Menunggu lawan…
      </p>
    </div>
  </div>

  <!-- ================= GAME ================= -->
  <template x-if="status === 'playing'">
    <div>

      <!-- TURN & TIMER -->
      <div class="flex flex-col sm:flex-row gap-3 sm:gap-6 items-start sm:items-center mb-4">
        <div class="font-semibold">
          Giliran:
          <span class="px-3 py-1 rounded"
            :class="canPlay() ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-600'"
            x-text="currentTurn">
          </span>
        </div>
        <div class="text-sm">
          ⏱️ Turn: <b x-text="turnTime"></b>s |
          ⌛ Game: <b x-text="gameTime"></b>s
        </div>
      </div>

      <!-- SCORE -->
      <div class="flex gap-6 mb-4 text-sm">
        <div>👤 <b x-text="player1"></b>: <b x-text="score1"></b></div>
        <div>👤 <b x-text="player2"></b>: <b x-text="score2"></b></div>
      </div>

      <!-- ================= GRID + DESKTOP CLUES ================= -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- GRID -->
        <div class="md:col-span-2 flex justify-center overflow-x-auto">
          <table class="border-collapse mx-auto">
            <template x-for="(row,y) in grid" :key="y">
              <tr>
                <template x-for="(cell,x) in row" :key="x">
                  <td class="w-11 h-11 sm:w-10 sm:h-10 border border-gray-400 relative bg-white">

                    <template x-if="numbers[`${y}_${x}`]">
                      <span class="absolute top-0 left-0 text-[10px] px-1 text-gray-600"
                        x-text="numbers[`${y}_${x}`]"></span>
                    </template>

                    <template x-if="cell !== null">
                      <input maxlength="1"
                        class="w-full h-full text-center uppercase outline-none"
                        :disabled="!canPlay() || lockedCells[`${y}_${x}`]"
                        :class="cellClass(y,x)"
                        x-model="inputs[y][x]"
                        @input="onInput(y,x)">
                    </template>

                  </td>
                </template>
              </tr>
            </template>
          </table>
        </div>

        <!-- DESKTOP CLUES -->
        <div class="hidden md:block text-sm">
          <h4 class="font-bold mb-2">➡️ Mendatar</h4>
          <template x-for="e in across" :key="e.number">
            <div class="mb-1">
              <b x-text="e.number"></b>. <span x-text="e.clue"></span>
            </div>
          </template>

          <h4 class="font-bold mt-4 mb-2">⬇️ Menurun</h4>
          <template x-for="e in down" :key="e.number">
            <div class="mb-1">
              <b x-text="e.number"></b>. <span x-text="e.clue"></span>
            </div>
          </template>
        </div>

      </div>

      <!-- ================= MOBILE CLUES ================= -->
      <div class="mt-6 md:hidden">
        <button
          @click="showClues = !showClues"
          class="w-full bg-gray-200 px-4 py-2 rounded text-sm font-semibold">
          Lihat Soal
        </button>

        <div x-show="showClues" class="mt-4 space-y-4 text-sm">
          <div>
            <h4 class="font-bold mb-2">➡️ Mendatar</h4>
            <template x-for="e in across" :key="e.number">
              <div class="mb-1">
                <b x-text="e.number"></b>. <span x-text="e.clue"></span>
              </div>
            </template>
          </div>

          <div>
            <h4 class="font-bold mb-2">⬇️ Menurun</h4>
            <template x-for="e in down" :key="e.number">
              <div class="mb-1">
                <b x-text="e.number"></b>. <span x-text="e.clue"></span>
              </div>
            </template>
          </div>
        </div>
      </div>

    </div>
  </template>

  <!-- ================= FINISHED ================= -->
  <template x-if="status === 'finished'">
    <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded text-center w-96">
        <h2 class="text-xl font-bold mb-3">🏁 Game Selesai</h2>

        <p class="mb-2">
          <b x-text="player1"></b>: <b x-text="score1"></b><br>
          <b x-text="player2"></b>: <b x-text="score2"></b>
        </p>

        <p class="font-semibold mb-4"
          x-text="score1===score2
            ? '🤝 Seri'
            : (myName===winner() ? '🏆 Kamu Menang' : '😢 Kamu Kalah')">
        </p>

        <a href="{{ route('dashboard') }}"
           class="inline-block px-4 py-2 bg-blue-600 text-white rounded">
          Kembali ke Dashboard
        </a>
      </div>
    </div>
  </template>

</div>

<script>
function ttsMultiplayer(){
  return {
    showClues: false,
    /* ================= STATE ================= */
    validatedWords: {},
    grid:[], entries:[], inputs:[],
    numbers:{}, across:[], down:[],
    lockedCells:{},

    lastInput: null,
    isTyping: false,
    typingTimer: null,

    roomCode:'{{ $room->room_code }}',
    myName:'{{ $player }}',

    status:'{{ $room->status }}',
    currentTurn:'{{ $room->current_turn }}',

    player1:'{{ $room->player1 }}',
    player2:'{{ $room->player2 }}',

    score1:0, score2:0,
    gameTime:0, turnTime:0,

    puzzleLoaded:false,
    rpsWaiting:false,
    isSyncing: false,
    validatingWord: false,



    winner(){
  if(this.score1 === this.score2) return null;
  return this.score1 > this.score2 ? this.player1 : this.player2;
},

    /* ================= HELPERS ================= */
    uniqueEntriesByStart(entries){
      const map = {};
      entries.forEach(e=>{
        const key = `${e.direction}_${e.y}_${e.x}`;
        if(!map[key]) map[key] = e;
      });
      return Object.values(map);
    },

    cellBelongsToValidatedWord(x, y){
      return this.entries.some((e, i) => {
        if(this.validatedWords[i] !== true) return false;
        for(let k=0;k<e.length;k++){
          const cx = e.direction==='across' ? e.x+k : e.x;
          const cy = e.direction==='down'   ? e.y+k : e.y;
          if(cx===x && cy===y) return true;
        }
        return false;
      });
    },

    /* ================= INIT ================= */
    init(){
      this.poll();
    },

    canPlay(){
      return this.status==='playing' && this.currentTurn===this.myName;
    },

    /* ================= POLL ================= */
    async poll(){
  setInterval(async () => {

    if(this.status === 'finished'){
      return;
    }

    let s;
    try {
      const res = await fetch(`/tts/room/${this.roomCode}/state`);
      if(!res.ok) return;
      s = await res.json();
    } catch {
      return;
    }

    const oldStatus = this.status;
    const oldTurn   = this.currentTurn;

    Object.assign(this,{
      status: s.status,
      player1: s.player1,
      player2: s.player2,
      score1: s.score1,
      score2: s.score2,
      gameTime: s.game_time,
      turnTime: s.turn_time
    });

    if(s.current_turn){
      this.currentTurn = s.current_turn;
    }

    // 🪨✂️📄 MASUK RPS
    if(this.status === 'rps'){
      this.rpsWaiting = false;
      return;
    }

    // 🔥 MASUK / SUDAH PLAYING TAPI PUZZLE BELUM ADA (PLAYER JOIN TELAT)
    if(this.status === 'playing' && !this.puzzleLoaded){
      await this.loadPuzzle();
      await this.syncCells(true);
      return;
    }

    // 🔄 GILIRAN BARU MASUK KE KITA
    if(oldTurn !== this.currentTurn && this.canPlay()){
      await this.syncCells(true);
      return;
    }

    // ⌨️ PLAYER AKTIF NGETIK → JANGAN GANGGU
    if(this.canPlay() && this.isTyping){
      return;
    }

    // 👀 PLAYER PASIF → SYNC RINGAN
    if(!this.canPlay() && oldTurn !== this.currentTurn){
      await this.syncCells(true);
    }

  }, 3500);
},

    /* ================= PUZZLE ================= */
    async loadPuzzle(){
      const d = await fetch(`/tts/room/${this.roomCode}/puzzle`).then(r=>r.json());
      if(!d.ready) return;

      this.grid = d.puzzle.grid;
      this.entries = d.puzzle.entries;
      this.inputs = this.grid.map(r=>r.map(c=>c===null?null:''));      

      this.entries.forEach(e=>{
        const key = `${e.y}_${e.x}`;
        if(this.numbers[key]===undefined){
          this.numbers[key] = e.number;
        }
      });

      this.across = this.uniqueEntriesByStart(this.entries.filter(e=>e.direction==='across'));
      this.down   = this.uniqueEntriesByStart(this.entries.filter(e=>e.direction==='down'));

      this.puzzleLoaded = true;
    },

    /* ================= RPS ================= */
    sendRps(choice){
      if(this.rpsWaiting) return;

      fetch(`/tts/room/${this.roomCode}/rps`,{
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body:JSON.stringify({
        player: this.myName,
        choice
      })
    })

      .then(r=>r.json())
      .then(d=>{
        if(d.waiting){
          this.rpsWaiting = true;
          return;
        }

        if(d.status==='playing'){
          this.status = 'playing';
          this.currentTurn = d.first_turn;
          this.rpsWaiting = false;
          this.puzzleLoaded = false;
          this.syncCells(); // 🔥 sinkron awal
        }
      });
    },

    /* ================= INPUT ================= */
    onInput(y,x){
      if(!this.canPlay()) return;

      this.lastInput = {x,y};
      this.isTyping = true;
      clearTimeout(this.typingTimer);
      this.typingTimer = setTimeout(()=>this.isTyping=false,300);

      this.$nextTick(()=>this.tryValidateWord(y,x));
    },

    /* ================= VALIDATION ================= */
    tryValidateWord(y, x){
  if(this.validatingWord) return;
  if(!this.lastInput) return;

  const affected = this.entries
    .map((e,i)=>({...e,__i:i}))
    .filter(e=>{
      for(let k=0;k<e.length;k++){
        const cx = e.direction==='across'?e.x+k:e.x;
        const cy = e.direction==='down'?e.y+k:e.y;
        if(cx===x && cy===y) return true;
      }
      return false;
    });

  for(const e of affected){
    const i = e.__i;
    if(this.validatedWords[i]) continue;

    let answer='', cells=[], filled=true, last=false;

    for(let k=0;k<e.length;k++){
      const cx = e.direction==='across'?e.x+k:e.x;
      const cy = e.direction==='down'?e.y+k:e.y;
      const v = this.inputs[cy][cx];
      if(!v){ filled=false; break; }
      if(cx===x && cy===y) last=true;
      answer += v.toUpperCase();
      cells.push({x:cx,y:cy});
    }

    if(!filled || !last) continue;

    // ❌ SALAH
    if(answer !== e.word){
      cells.forEach(c=>{
        const k=`${c.y}_${c.x}`;
        if(this.cellBelongsToValidatedWord(c.x,c.y)) return;
        if(this.lockedCells[k]===true) return;
        this.lockedCells[k]='wrong';
        this.inputs[c.y][c.x]='';
      });

      setTimeout(()=>{
        cells.forEach(c=>{
          const k=`${c.y}_${c.x}`;
          if(this.lockedCells[k]==='wrong') delete this.lockedCells[k];
        });
      },200);

      return; // 🔥 STOP TOTAL
    }

    this.submitWord(i, answer, cells);
    return; // 🔥 STOP SETELAH 1 SUBMIT
  }
},

    /* ================= SUBMIT ================= */
    submitWord(i, answer, cells){
  // ⛔ cegah double submit
  if(this.validatingWord) return;

  this.validatingWord = true;
  this.validatedWords[i] = 'pending';

  fetch(`/tts/room/${this.roomCode}/check-word`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      word_index: i,
      player: this.myName,
      cells: cells.map(c => ({
        x: c.x,
        y: c.y,
        letter: this.inputs[c.y][c.x]
      }))
    })
  })
  .then(r => r.json())
  .then(async res => {

    // =========================
    // ✅ BENAR
    // =========================
    if(res.status === 'correct'){
      this.validatedWords[i] = true;
      this.lastInput = null;
      this.isTyping = false;
      this.validatingWord = false;

      // 🔥 kasih jeda biar user lihat warna
      setTimeout(async () => {
        await this.syncCells(true);
        this.currentTurn = res.next_turn;
      }, 300);

      // ⏭️ update turn dari server
      this.currentTurn = res.next_turn;
      return;
    }

    // =========================
    // ❌ SALAH
    // =========================
    if(res.status === 'wrong'){
      delete this.validatedWords[i];
      this.isTyping = false;
      this.validatingWord = false;
      return;
    }

    // =========================
    // 🔒 SUDAH DI-LOCK PLAYER LAIN
    // =========================
    if(res.status === 'locked'){
      this.validatingWord = false;
      await this.syncCells(true);
    }
  })
  .catch(() => {
    // ⛔ safety reset
    this.validatingWord = false;
    this.isTyping = false;
  });
},

    /* ================= SYNC ================= */
  async syncCells(force = false){
  if(this.isSyncing) return;
  this.isSyncing = true;

  try {
    const cells = await fetch(`/tts/room/${this.roomCode}/cells`)
      .then(r => r.json());

    // 🔥 RESET TOTAL GRID
    this.lockedCells = {};
    this.inputs = this.grid.map(row =>
      row.map(cell => cell === null ? null : '')
    );

    // 🔥 ISI ULANG DARI SERVER (SOURCE OF TRUTH)
    cells.forEach(c => {
      const key = `${c.y}_${c.x}`;
      this.inputs[c.y][c.x] = c.letter ?? '';
      if(c.locked){
        this.lockedCells[key] = true;
      }
    });

  } finally {
    this.isSyncing = false;
  }
},

    /* ================= CLEAN ================= */
    clearPartialInputs(){
      if(!this.lastInput) return;
      const {x,y}=this.lastInput;

      this.entries.forEach((e,i)=>{
        if(this.validatedWords[i]) return;
        for(let k=0;k<e.length;k++){
          const cx=e.direction==='across'?e.x+k:e.x;
          const cy=e.direction==='down'?e.y+k:e.y;
          if(cx===x && cy===y){
            const kk=`${cy}_${cx}`;
            if(!this.lockedCells[kk]){
              this.inputs[cy][cx]='';
            }
          }
        }
      });
    },

    cellClass(y,x){
      const k=`${y}_${x}`;
      if(this.lockedCells[k]==='wrong') return 'bg-red-400 text-white font-bold';
      if(this.lockedCells[k]) return 'bg-green-300 font-bold';
      return this.canPlay()?'focus:bg-yellow-100':'bg-gray-200';
    }
  }
}
</script>

@endsection
