@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white rounded shadow text-center">

  <h2 class="text-2xl font-bold mb-6">🧩 Teka-Teki Silang Rohani</h2>

  <div class="grid grid-cols-2 gap-4">

    <!-- SINGLE PLAYER -->
    <a href="{{ route('tts.index') }}"
       class="border rounded p-6 hover:bg-green-50 transition">
      <div class="text-4xl mb-2">👤</div>
      <h3 class="font-semibold text-lg">Single Player</h3>
      <p class="text-sm text-gray-500">
        Main sendiri melawan waktu
      </p>
    </a>

    <!-- MULTIPLAYER -->
    <a href="{{ route('tts.multiplayer.lobby') }}"
       class="border rounded p-6 hover:bg-blue-50 transition">
      <div class="text-4xl mb-2">👥</div>
      <h3 class="font-semibold text-lg">Multiplayer</h3>
      <p class="text-sm text-gray-500">
        Lawan teman 1 vs 1
      </p>
    </a>

  </div>

</div>
@endsection
