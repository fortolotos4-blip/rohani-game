@extends('layouts.app')

@section('content')
<div x-data="ttsApp()" x-init="init()"
     class="max-w-5xl mx-auto p-4 sm:p-6 bg-white rounded shadow">

  <!-- HEADER -->
  <div class="flex justify-between items-center mb-4 sticky top-0 bg-white z-10">
    <h2 class="text-xl sm:text-2xl font-bold">🧩 TTS Rohani</h2>
    <div class="font-semibold text-sm sm:text-base">
      ⏱ <span x-text="timeLeft"></span>s
    </div>
  </div>

  <!-- DIFFICULTY -->
  <div class="flex gap-2 mb-4 text-sm">
    <a href="/tts?difficulty=easy" class="px-3 py-1 bg-green-500 text-white rounded">Easy</a>
    <a href="/tts?difficulty=medium" class="px-3 py-1 bg-yellow-500 text-white rounded">Medium</a>
    <a href="/tts?difficulty=hard" class="px-3 py-1 bg-red-500 text-white rounded">Hard</a>
  </div>

  <!-- GRID + CLUES -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- GRID -->
    <div class="md:col-span-2 flex justify-center overflow-x-auto">
      <table class="border-collapse mx-auto">
        <template x-for="(row,y) in grid" :key="y">
          <tr>
            <template x-for="(cell,x) in row" :key="x">
              <td class="w-11 h-11 sm:w-10 sm:h-10 border border-gray-400 relative">

                <template x-if="numbers[`${y}_${x}`]">
                  <span class="absolute top-0 left-0 text-[10px] px-1"
                        x-text="numbers[`${y}_${x}`]"></span>
                </template>

                <template x-if="cell !== null">
                  <input maxlength="1"
                         class="w-full h-full text-center uppercase outline-none"
                         :disabled="lockedCells[`${y}_${x}`]"
                         :class="cellClass(y,x)"
                         x-model="inputs[y][x]"
                         @input="onInput">
                </template>

              </td>
            </template>
          </tr>
        </template>
      </table>
    </div>

    <!-- CLUES DESKTOP -->
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

  <!-- CLUES MOBILE -->
  <div class="mt-6 md:hidden">
    <button @click="showClues = !showClues"
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

  <!-- GAME OVER -->
  <div x-show="gameOver"
     x-cloak
     x-transition.opacity
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <div class="bg-white p-6 rounded text-center w-80">
      <h2 class="text-xl font-bold mb-2"
          x-text="resultType === 'win' ? '🎉 Selamat!' : '⏰ Waktu Habis'"></h2>

      <p class="mb-3">
        Jawaban benar:
        <b x-text="Object.keys(solvedWords).length"></b> /
        <b x-text="countPlayableEntries()"></b>
      </p>

      <a href="{{ route('dashboard') }}"
         class="px-4 py-2 bg-blue-600 text-white rounded">
        Kembali ke Dashboard
      </a>
    </div>
  </div>

</div>

<script>
function ttsApp(){
  return {
    grid: @json($puzzle['grid']),
    entries: @json($puzzle['entries']),
    difficulty: '{{ $difficulty }}',

    inputs: [],
    numbers: {},
    across: [],
    down: [],
    wordStatus: {},
    lockedCells: {},

    timeLeft: {{ $difficulty==='easy' ? 300 : ($difficulty==='medium' ? 250 : 200) }},
    timer: null,

    showClues: false,

    gameOver: false,
    resultType: null,

    solvedWords: {},

    init(){
      this.gameOver = false;
      this.resultType = null;
      this.solvedWords = {};
      this.wordStatus = {};
      this.lockedCells = {};

      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
        }

      this.inputs = this.grid.map(r => r.map(c => c === null ? null : ''));

      // 🔥 hanya entry bernomor
      const numbered = this.entries.filter(e => e.number !== undefined);

      numbered.forEach(e => {
        this.numbers[`${e.y}_${e.x}`] = e.number;
      });

      this.across = numbered.filter(e => e.direction === 'across');
      this.down   = numbered.filter(e => e.direction === 'down');

      this.startTimer();
    },

    startTimer(){
  // 🔒 pastikan tidak ada timer ganda
  if (this.timer) {
    clearInterval(this.timer);
    this.timer = null;
  }

  this.timer = setInterval(() => {
    this.timeLeft--;

    if (this.timeLeft <= 0) {
      clearInterval(this.timer);
      this.timer = null;
      this.gameOver = true;
      this.resultType = 'lose';
    }
  }, 1000);
},

    onInput(){
      this.validateWords();
    },

    validateWords(){
      this.entries.forEach((e,i)=>{
        if(e.number === undefined) return;
        if(this.solvedWords[i]) return;

        let ans = '';
        let cells = [];
        let filled = true;

        for(let k=0;k<e.length;k++){
          const x = e.direction==='across'?e.x+k:e.x;
          const y = e.direction==='down'?e.y+k:e.y;

          const val = (this.inputs[y][x] || '').toUpperCase();
          ans += val;
          cells.push({x,y});

          if(!val) filled = false;
        }

        if(!filled) return;

        if(ans === e.word){
          this.wordStatus[i] = 'correct';
          this.solvedWords[i] = true;

          cells.forEach(c=>{
            this.lockedCells[`${c.y}_${c.x}`] = true;
          });

        } else {
          this.wordStatus[i] = 'wrong';

          setTimeout(()=>{
            cells.forEach(c=>{
              if(!this.lockedCells[`${c.y}_${c.x}`]){
                this.inputs[c.y][c.x] = '';
              }
            });
            delete this.wordStatus[i];
          },700);
        }
      });

      this.checkFinish();
    },

    checkFinish(){
      const solved = Object.keys(this.solvedWords).length;
      const total  = this.countPlayableEntries();

      if(solved === total && total > 0){
        clearInterval(this.timer);
        this.gameOver = true;
        this.resultType = 'win';
      }
    },

    countPlayableEntries(){
      return this.entries.filter(e => e.number !== undefined).length;
    },

    cellClass(y,x){
      for(const [i,e] of this.entries.entries()){
        if(e.number === undefined) continue;

        for(let k=0;k<e.length;k++){
          const cx=e.direction==='across'?e.x+k:e.x;
          const cy=e.direction==='down'?e.y+k:e.y;
          if(cx===x && cy===y){
            if(this.wordStatus[i]==='correct') return 'bg-green-300 font-bold';
            if(this.wordStatus[i]==='wrong') return 'bg-red-300 font-bold';
          }
        }
      }
      return this.lockedCells[`${y}_${x}`]
        ? 'bg-green-200 font-bold'
        : 'focus:bg-yellow-100';
    }
  }
}
</script>
@endsection
