<div
  x-data="ttsMultiplayer()"
  x-init="init()"
  class="max-w-5xl mx-auto p-4 bg-white rounded shadow"
>

  <!-- HEADER -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">🧩 TTS Rohani</h2>
    <div class="text-sm font-semibold">⏱ <span x-text="gameTime"></span>s</div>
  </div>

  <!-- TURN INFO -->
  <div class="mb-4 font-semibold">
    Giliran:
    <span
      class="px-3 py-1 rounded"
      :class="canPlay()
        ? 'bg-green-100 text-green-700'
        : 'bg-gray-200 text-gray-600'"
      x-text="currentTurn"
    ></span>
  </div>

  <!-- GRID -->
  <div class="flex justify-center overflow-x-auto">
    <table class="border-collapse">
      <template x-for="(row,y) in grid" :key="y">
        <tr>
          <template x-for="(cell,x) in row" :key="x">
            <td class="w-11 h-11 border border-gray-400 relative bg-white">

              <!-- NUMBER -->
              <template x-if="numbers[`${y}_${x}`]">
                <span class="absolute top-0 left-0 text-[10px] px-1 text-gray-600"
                  x-text="numbers[`${y}_${x}`]"></span>
              </template>

              <!-- INPUT -->
              <template x-if="cell !== null">
                <input
                  maxlength="1"
                  class="w-full h-full text-center uppercase outline-none"
                  :disabled="!canPlay() || lockedCells[`${y}_${x}`]"
                  :class="cellClass(y,x)"
                  x-model="inputs[y][x]"
                  @input="onInput(y,x)"
                >
              </template>

            </td>
          </template>
        </tr>
      </template>
    </table>
  </div>

  <!-- BUTTON LIHAT SOAL -->
  <div class="mt-6">
    <button
      @click="showClues = !showClues"
      class="w-full bg-gray-200 px-4 py-3 rounded-lg
             text-sm font-semibold flex justify-center items-center gap-2"
    >
      📜 <span x-text="showClues ? 'Tutup Soal' : 'Lihat Soal'"></span>
    </button>
  </div>

  <!-- CLUES -->
  <div
    x-show="showClues"
    x-transition
    class="mt-4 bg-white rounded-lg p-4 text-sm space-y-4"
  >

    <!-- MENDATAR -->
    <div>
      <h4 class="font-bold mb-2 flex items-center gap-2">➡️ Mendatar</h4>
      <template x-for="e in across" :key="e.number">
        <div
          @click="focusEntry(e)"
          class="py-1 cursor-pointer hover:bg-gray-100 rounded px-1"
        >
          <b x-text="e.number"></b>. <span x-text="e.clue"></span>
        </div>
      </template>
    </div>

    <!-- MENURUN -->
    <div>
      <h4 class="font-bold mb-2 flex items-center gap-2">⬇️ Menurun</h4>
      <template x-for="e in down" :key="e.number">
        <div
          @click="focusEntry(e)"
          class="py-1 cursor-pointer hover:bg-gray-100 rounded px-1"
        >
          <b x-text="e.number"></b>. <span x-text="e.clue"></span>
        </div>
      </template>
    </div>

  </div>

</div>

<script>
function ttsMultiplayer(){
  return {
    // DATA DARI SERVER
    grid: @json($puzzle->grid ?? []),
    entries: @json($puzzle->entries ?? []),

    roomCode: '{{ $room->room_code }}',
    myName: '{{ request("player") }}',
    currentTurn: '{{ $room->current_turn }}',

    inputs: [],
    timerId: null,

    init(){
      this.prepareInputs();
      this.pollTurn();
    },

    prepareInputs(){
      this.inputs = this.grid.map(r => r.map(()=>'' ));
    },

    // ======================
    // TURN LOGIC
    // ======================
    canPlay(){
      return this.currentTurn === this.myName;
    },

    pollTurn(){
      setInterval(() => {
        fetch(`/tts/room/${this.roomCode}/state`)
          .then(r=>r.json())
          .then(data=>{
            this.currentTurn = data.current_turn;
          });
      }, 1500);
    },

    // ======================
    // INPUT
    // ======================
    onInput(){
      // setelah input → langsung ganti giliran
      this.endTurn();
    },

    endTurn(){
      fetch(`/tts/room/${this.roomCode}/turn`, {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        }
      });
    },

    // ======================
    // CELL STYLE
    // ======================
    cellClass(){
      if(!this.canPlay()){
        return 'bg-gray-100 cursor-not-allowed';
      }
      return 'focus:bg-yellow-100';
    }
  }
}
</script>
