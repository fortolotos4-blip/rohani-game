@extends('layouts.app')

@section('content')
<div
  x-data="multiplayerLobby('{{ $roomCode }}')"
  x-init="init()"
  class="max-w-xl mx-auto p-4"
>

  <!-- ROOM INFO -->
  <div class="bg-white rounded shadow p-4 mb-4 text-center">
    <h2 class="text-lg font-bold mb-1">Lobby Multiplayer</h2>
    <p class="text-sm text-gray-600">
      Kode Room:
      <span class="font-mono font-bold text-indigo-600" x-text="room.code"></span>
    </p>
    <p class="mt-2 text-sm font-semibold" x-text="statusText"></p>
  </div>

  <!-- PLAYER LIST -->
  <div class="bg-white rounded shadow p-4 mb-4">
    <h3 class="font-semibold mb-3">Pemain</h3>

    <div class="grid grid-cols-2 gap-2">
      <template x-for="(player, index) in players" :key="player.id">
        <div class="px-3 py-2 bg-gray-100 rounded text-sm font-medium">
          <span x-text="player.player_name"></span>
        </div>
      </template>

      <!-- SLOT KOSONG -->
      <template x-for="n in emptySlots" :key="'empty-'+n">
        <div class="px-3 py-2 border border-dashed rounded text-sm text-gray-400 text-center">
          Menunggu…
        </div>
      </template>
    </div>
  </div>

  <!-- PICKING OVERLAY -->
  <div
    x-show="room.status === 'picking'"
    x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded p-6 w-80 text-center">
      <h3 class="text-lg font-bold mb-4">Undian Giliran</h3>
      <p class="text-sm mb-4 text-gray-600">
        Pilih satu kotak untuk menentukan urutan.
      </p>

      <div
        class="grid grid-cols-2 gap-3"
      >
        <template x-for="(box, i) in players.length" :key="i">
          <button
          @click="pickBox(i)"
          :disabled="hasPicked || takenPicks.includes(i + 1)"
          class="h-14 rounded border text-xl font-bold
                bg-gray-200 hover:bg-gray-300 disabled:opacity-40"
        >
          <span x-text="takenPicks.includes(i + 1) ? '⛔' : '❓'"></span>
        </button>

        </template>
      </div>

      <p class="text-xs text-gray-500 mt-4" x-show="hasPicked">
        Menunggu pemain lain…
      </p>
    </div>
  </div>

</div>

<script>
function multiplayerLobby(roomCode){
  return {
    room: {
      code: roomCode,
      status: 'waiting',
      max_players: 4
    },
    players: [],
    mePicked: false,
    pollId: null,
    takenPicks: [],

    init(){
  this.fetchLobby();
  this.mePicked = localStorage.getItem('picked_'+this.room.code) === '1';

  this.pollId = setInterval(this.fetchLobby, 3000);
},

    fetchLobby(){
  fetch(`/api/multiplayer/lobby/${this.room.code}`)
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    })
    .then(data => {
      this.room = {
        ...data.room,
        code: data.room.room_code
      };
      this.players = data.players;
      this.takenPicks = data.taken_picks ?? [];

      // ✅ SERVER = SOURCE OF TRUTH UNTUK PICK
      const me = {{ session('multiplayer_player_id') ?? 'null' }};
      if (me) {
        const myPick =
          this.players.find(p => p.id === me)?.pick_order;
        this.mePicked = !!myPick;
      }

      if (this.room.status === 'playing') {
        clearInterval(this.pollId);
        window.location.href =
          `/multiplayer/game/${this.room.code}`;
      }
    })
    .catch(err => {
      console.warn('fetchLobby failed:', err.message);
      // ⛔ jangan ubah state saat error (anti flicker)
    });
},

    pickBox(index){
  if (this.mePicked) return;

  this.mePicked = true;
  localStorage.setItem('picked_'+this.room.code, '1');

  fetch('/api/multiplayer/pick', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({
      room_code: this.room.code,
      pick: index + 1
    })
  })
  .catch(() => {
    // jika gagal, buka kembali (opsional)
    this.mePicked = false;
    localStorage.removeItem('picked_'+this.room.code);
  });
},

    get emptySlots(){
      return Math.max(this.room.max_players - this.players.length, 0);
    },

    get hasPicked(){
      return this.mePicked;
    },

    get statusText(){
      switch(this.room.status){
        case 'waiting': return 'Menunggu pemain lain…';
        case 'picking': return 'Undian giliran sedang berlangsung';
        case 'playing': return 'Game dimulai';
        default: return '';
      }
    }
  }
}
</script>
@endsection
