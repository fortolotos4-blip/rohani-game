@extends('layouts.app')

@section('sidebar')
  <a href="{{ route('dashboard') }}" class="block py-2">Back</a>
@endsection

@section('content')
<div x-data="puzzleApp()" x-init="init()" class="max-w-4xl mx-auto p-4">
  <h2 class="text-2xl font-bold mb-4">Puzzle Gambar</h2>

  <div class="bg-white p-4 rounded shadow mb-4">
    <div class="flex gap-4 items-center">
      <div>
        <label class="text-sm block mb-1">Pilih gambar</label>
        <select x-model="selectedImage" class="border rounded px-2 py-1">
          <template x-for="img in images" :key="img">
            <option :value="img.path" x-text="img.label"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="text-sm block mb-1">Ukuran grid</label>
        <select x-model.number="gridSize" class="border rounded px-2 py-1">
          <option value="3">3 × 3</option>
          <option value="4">4 × 4</option>
          <option value="5">5 × 5</option>
        </select>
      </div>

      <div class="ml-auto flex gap-2">
        <button @click="shuffle()" class="px-3 py-2 bg-yellow-500 text-white rounded">Shuffle / Mulai</button>
        <button @click="reset()" class="px-3 py-2 bg-gray-500 text-white rounded">Reset</button>
      </div>
    </div>

    <div class="mt-3 text-sm text-gray-600 flex gap-4">
      <div>Waktu: <strong x-text="formatTime(timer)"></strong></div>
      <div>Moves: <strong x-text="moves"></strong></div>
      <div>Status: <strong x-text="statusText"></strong></div>
    </div>
  </div>

  <div class="bg-white p-6 rounded shadow">
    <div class="flex justify-center">
      <div class="relative" :style="`width:${boardSize}px; height:${boardSize}px;`">
        <!-- Grid container -->
        <template x-for="(cell, idx) in cells" :key="idx">
          <div
            @click="onTileClick(idx)"
            x-show="cell !== null"
            :style="tileStyle(idx)"
            class="absolute border box-border select-none transition-all duration-150"
            :class="{'cursor-pointer': isMovable(idx)}"
          ></div>
        </template>

        <!-- Empty tile - hidden / invisible -->
        <div x-show="false"></div>
      </div>
    </div>
  </div>

  <!-- Modal win -->
  <div x-show="showWin" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Selamat! Anda menyelesaikan puzzle</h3>
      <p class="mt-2">Waktu: <strong x-text="formatTime(timer)"></strong></p>
      <p class="mt-1">Moves: <strong x-text="moves"></strong></p>
      <div class="mt-4 text-right">
        <button @click="closeWin()" class="px-3 py-2 bg-indigo-600 text-white rounded">Tutup</button>
      </div>
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
      // set default board size responsif: gunakan 420px atau 90% layar kecil
      const w = Math.min(540, Math.floor(window.innerWidth * 0.9));
      this.boardSize = w;
      this.reset();
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
