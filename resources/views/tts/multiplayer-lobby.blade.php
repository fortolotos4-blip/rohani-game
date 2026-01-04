@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto p-6 bg-white shadow rounded">

<h2 class="font-bold text-xl mb-4">Multiplayer TTS</h2>

<form method="POST" action="/tts/room/create">
@csrf
<input name="player" class="border p-2 w-full mb-2" placeholder="Nama">
<button class="bg-green-600 text-white w-full py-2">Buat Room</button>
</form>

<div class="flex items-center my-6">
  <div class="flex-grow border-t"></div>
  <span class="mx-4 text-xs text-gray-500 font-semibold">
    ATAU
  </span>
  <div class="flex-grow border-t"></div>
</div>


<form method="POST" action="/tts/room/join">
@csrf
<input name="player" class="border p-2 w-full mb-2" placeholder="Nama">
<input name="room_code" class="border p-2 w-full mb-2" placeholder="Kode Room">
<button class="bg-blue-600 text-white w-full py-2">Gabung</button>
</form>

</div>
@endsection
