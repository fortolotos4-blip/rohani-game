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
    pollId: null,

    init(){
      this.fetchLobby();
      this.pollId = setInterval(() => this.fetchLobby(), 3000);
    },

    fetchLobby(){
      fetch(`/multiplayer/lobby-state/${this.room.code}`)
        .then(r => r.json())
        .then(data => {
          this.room.status = data.room.status;
          this.room.max_players = data.room.max_players;
          this.players = data.players;

          if (this.room.status === 'playing') {
            clearInterval(this.pollId);
            window.location.href =
              `/multiplayer/game/${this.room.code}`;
          }
        });
    },

    get emptySlots(){
      return Math.max(this.room.max_players - this.players.length, 0);
    },

    get statusText(){
      return this.room.status === 'waiting'
        ? 'Menunggu pemain lain…'
        : 'Game dimulai…';
    }
  }
}
</script>
@endsection
