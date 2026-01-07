@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6">

  <h2 class="text-2xl font-bold mb-6 text-center">
    Multiplayer Tebak Gambar
  </h2>

  <!-- CREATE ROOM -->
  <div class="bg-white rounded shadow p-4 mb-6">
    <h3 class="font-semibold mb-3">Buat Room</h3>

    <form
      x-data="createRoomForm()"
      @submit.prevent="submit"
      class="space-y-3"
    >
      <input
        x-model="player_name"
        required
        maxlength="30"
        class="w-full border rounded px-3 py-2"
        placeholder="Nama pemain"
      >

      <select
        x-model="max_players"
        class="w-full border rounded px-3 py-2"
      >
        <option value="2">2 Pemain</option>
        <option value="3">3 Pemain</option>
        <option value="4">4 Pemain</option>
      </select>

      <button
        class="w-full bg-indigo-600 text-white rounded py-2 font-semibold"
      >
        Buat Room
      </button>
    </form>
  </div>

  <!-- JOIN ROOM -->
  <div class="bg-white rounded shadow p-4">
    <h3 class="font-semibold mb-3">Gabung Room</h3>

    <form
      x-data="joinRoomForm()"
      @submit.prevent="submit"
      class="space-y-3"
    >
      <input
        x-model="player_name"
        required
        maxlength="30"
        class="w-full border rounded px-3 py-2"
        placeholder="Nama pemain"
      >

      <input
        x-model="room_code"
        required
        class="w-full border rounded px-3 py-2 uppercase"
        placeholder="Kode Room"
      >

      <button
        class="w-full bg-green-600 text-white rounded py-2 font-semibold"
      >
        Gabung Room
      </button>
    </form>
  </div>

</div>

<script>
function createRoomForm(){
  return {
    player_name: '',
    max_players: 2,

    submit(){
      fetch('/api/multiplayer/create', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          player_name: this.player_name,
          max_players: this.max_players
        })
      })
      .then(r => r.json())
      .then(d => {
        if(d.room_code){
          window.location.href =
            `/multiplayer/lobby/${d.room_code}`;
        }
      });
    }
  }
}

function joinRoomForm(){
  return {
    player_name: '',
    room_code: '',

    submit(){
      fetch('/api/multiplayer/join', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          player_name: this.player_name,
          room_code: this.room_code.toUpperCase()
        })
      })
      .then(r => r.json())
      .then(d => {
        if(d.success){
          window.location.href =
            `/multiplayer/lobby/${this.room_code.toUpperCase()}`;
        }
      });
    }
  }
}
</script>
@endsection
