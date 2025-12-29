@extends('layouts.app')

@section('content')
<div 
  x-data="puzzleApp()" 
  x-init="init()" 
  class="max-w-5xl mx-auto px-4 py-6 space-y-6"
>

  <!-- ================= HEADER ================= -->
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-extrabold flex items-center gap-2">
      🧩 Puzzle Gambar
    </h2>

    <span
      class="px-3 py-1 rounded-full text-sm font-semibold"
      :class="started ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
      x-text="statusText"
    ></span>
  </div>

  <!-- ================= CONTROL PANEL ================= -->
  <div class="bg-white rounded-xl shadow p-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">

      <div>
        <label class="text-sm font-semibold text-gray-600 block mb-1">
          Gambar
        </label>
        <select x-model="selectedImage"
          class="w-full border rounded-lg px-3 py-2 text-sm">
          <template x-for="img in images" :key="img.path">
            <option :value="img.path" x-text="img.label"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold text-gray-600 block mb-1">
          Ukuran Grid
        </label>
        <select x-model.number="gridSize"
          class="w-full border rounded-lg px-3 py-2 text-sm">
          <option value="3">3 × 3</option>
          <option value="4">4 × 4</option>
          <option value="5">5 × 5</option>
        </select>
      </div>

      <div class="flex gap-2 sm:justify-end">
        <button
          @click="shuffle()"
          class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold">
          🔀 Mulai
        </button>

        <button
          @click="reset()"
          class="flex-1 sm:flex-none px-4 py-2 bg-gray-500 text-white rounded-lg">
          Reset
        </button>
      </div>
    </div>

    <!-- INFO -->
    <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-600">
      <div>⏱ <b x-text="formatTime(timer)"></b></div>
      <div>🔢 Moves: <b x-text="moves"></b></div>
    </div>
  </div>

  <!-- ================= PUZZLE BOARD ================= -->
  <div class="bg-white rounded-xl shadow p-4 flex justify-center">
    <div class="w-full max-w-md aspect-square relative">

      <template x-for="(cell, idx) in cells" :key="idx">
        <div
          x-show="cell !== null"
          @click="onTileClick(idx)"
          :style="tileStyle(idx)"
          class="absolute rounded-lg shadow-md transition-all duration-200
                 border border-gray-200
                 hover:scale-[1.02]"
          :class="isMovable(idx) ? 'cursor-pointer' : 'opacity-80'"
        ></div>
      </template>

    </div>
  </div>

  <!-- ================= WIN MODAL ================= -->
  <div
    x-show="showWin"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-xl p-6 w-80 text-center">
      <h3 class="text-xl font-bold mb-2">🎉 Puzzle Selesai!</h3>

      <p class="text-sm text-gray-600">
        Waktu: <b x-text="formatTime(timer)"></b><br>
        Moves: <b x-text="moves"></b>
      </p>

      <button
        @click="closeWin()"
        class="mt-4 w-full px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold"
      >
        Tutup
      </button>
    </div>
  </div>

</div>

<script>
function puzzleApp(){
  return {
    images: @json($images ?? []), // setiap item: {label, path}
    selectedImage: @json($images[0]['path'] ?? null), // simpan path sebagai value
    gridSize: 4,
    cells: [], // array of tile indices or null for blank
    blankIndex: null,
    boardSize: 420, // px (will be adjusted)
    tileSize: 0,
    timer: 0,
    timerInterval: null,
    moves: 0,
    started: false,
    showWin: false,
    statusText: 'Belum mulai',

    init(){
      this.reset();

      window.addEventListener('resize', () => {
    this.calculateTileSize();
  });
    },

    reset(){
      // stop timer
      if(this.timerInterval) { clearInterval(this.timerInterval); this.timerInterval = null; }
      this.timer = 0;
      this.moves = 0;
      this.started = false;
      this.showWin = false;
      this.statusText = 'Belum mulai';
      // create solved cells
      const n = this.gridSize * this.gridSize;
      this.cells = [];
      for(let i=0;i<n;i++){
        // last cell is null (blank)
        this.cells.push(i === n-1 ? null : i);
      }
      this.blankIndex = n-1;
      this.calculateTileSize();
    },

    calculateTileSize(){
      const board = document.querySelector('.aspect-square');
      this.boardSize = board.offsetWidth;
      this.tileSize = Math.floor(this.boardSize / this.gridSize);
    },

    startTimer(){
      if(this.timerInterval) clearInterval(this.timerInterval);
      this.timerInterval = setInterval(()=>{ this.timer++; }, 1000);
    },

    formatTime(s){
      const mm = Math.floor(s/60).toString().padStart(2,'0');
      const ss = (s%60).toString().padStart(2,'0');
      return `${mm}:${ss}`;
    },

    shuffle(){
      // Build initial array [0..n-2, null]
      const n = this.gridSize * this.gridSize;
      let arr = [];
      for(let i=0;i<n-1;i++) arr.push(i);
      arr.push(null);

      // Fisher-Yates shuffle but ensure solvable by checking inversion parity
      do {
        // shuffle array
        for(let i=arr.length-1;i>0;i--){
          const j = Math.floor(Math.random()*(i+1));
          [arr[i], arr[j]] = [arr[j], arr[i]];
        }
      } while(!this.isSolvable(arr));

      this.cells = arr.slice();
      this.blankIndex = this.cells.indexOf(null);
      this.moves = 0;
      this.timer = 0;
      this.started = true;
      this.statusText = 'Berjalan';
      this.startTimer();
    },

    // check solvable for sliding puzzle
    isSolvable(arr){
      // count inversions (ignoring null)
      const flat = arr.filter(x => x !== null);
      let inv = 0;
      for(let i=0;i<flat.length;i++){
        for(let j=i+1;j<flat.length;j++){
          if(flat[i] > flat[j]) inv++;
        }
      }
      if(this.gridSize % 2 === 1){
        // odd grid: solvable if inversions even
        return inv % 2 === 0;
      } else {
        // even grid: determine row of blank from bottom (1-based)
        const blankPos = arr.indexOf(null);
        const rowFromTop = Math.floor(blankPos / this.gridSize) + 1;
        const rowFromBottom = this.gridSize - (rowFromTop - 1);
        // solvable if (inversions + rowFromBottom) is even
        return (inv + rowFromBottom) % 2 === 0;
      }
    },

    // helper: is tile at idx movable (adjacent to blank)
    isMovable(idx){
      if(this.blankIndex === null) return false;
      const r1 = Math.floor(idx / this.gridSize), c1 = idx % this.gridSize;
      const r2 = Math.floor(this.blankIndex / this.gridSize), c2 = this.blankIndex % this.gridSize;
      const manhattan = Math.abs(r1 - r2) + Math.abs(c1 - c2);
      return manhattan === 1;
    },

    onTileClick(idx){
      if(!this.started) return;
      if(!this.isMovable(idx)) return;
      // swap tile and blank
      [this.cells[idx], this.cells[this.blankIndex]] = [this.cells[this.blankIndex], this.cells[idx]];
      this.blankIndex = idx;
      this.moves++;
      // check win: all tiles in order (last null)
      if(this.checkWin()){
        this.statusText = 'Selesai';
        this.started = false;
        if(this.timerInterval) clearInterval(this.timerInterval);
        this.showWin = true;
      }
    },

    checkWin(){
      for(let i=0;i<this.cells.length;i++){
        const expected = i === this.cells.length - 1 ? null : i;
        if(this.cells[i] !== expected) return false;
      }
      return true;
    },

    // compute tile css style: size, background-position based on tile number
    tileStyle(idx){
      const size = this.tileSize;
      const left = (idx % this.gridSize) * size;
      const top = Math.floor(idx / this.gridSize) * size;
      const cell = this.cells[idx];
      // if null (blank) we hide but tileStyle called only for visible cells
const bgUrl = this.selectedImage ? `/${this.selectedImage}` : '/images/placeholder.png';
      // compute background position using tile number (cell)
      // each tile shows portion corresponding to its correct position (cell)
      const total = this.gridSize;
      const tileNo = cell; // 0..n-2
      const bgSizePx = this.gridSize * size;
      const col = tileNo % total;
      const row = Math.floor(tileNo / total);
      const posX = -(col * size);
      const posY = -(row * size);

      return `left:${left}px; top:${top}px; width:${size}px; height:${size}px;
              background-image: url('${bgUrl}');
              background-size: ${bgSizePx}px ${bgSizePx}px;
              background-position: ${posX}px ${posY}px;`;
    },

    closeWin(){
      this.showWin = false;
      // keep solved board visible (or reset)
    }
  }
}
</script>

<style>
/* small responsive tweak: make tile click show pointer */
.puzzle-tile { cursor:pointer; }
/* make absolute children smooth */
</style>
@endsection
