<div 
  x-data="ttsMultiplayer()" 
  x-init="init()" 
  class="mt-6"
>

  <!-- TURN INFO -->
  <div class="mb-4 text-lg font-semibold">
    Giliran:
    <span 
      class="px-3 py-1 rounded"
      :class="canPlay() ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
      x-text="currentTurn"
    ></span>
  </div>

  <!-- GRID -->
  <table class="border-collapse mx-auto">
    <template x-for="(row,y) in grid" :key="y">
      <tr>
        <template x-for="(cell,x) in row" :key="x">
          <td class="w-10 h-10 border border-gray-400 relative">

            <template x-if="cell !== null">
              <input
                maxlength="1"
                class="w-full h-full text-center uppercase outline-none"
                :disabled="!canPlay()"
                :class="cellClass(y,x)"
                x-model="inputs[y][x]"
                @input="onInput"
              >
            </template>

          </td>
        </template>
      </tr>
    </template>
  </table>

  <!-- INFO -->
  <div class="mt-4 text-sm text-gray-600 text-center">
    <template x-if="!canPlay()">
      <div>⛔ Menunggu giliran lawan...</div>
    </template>
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
