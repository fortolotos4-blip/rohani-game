@extends('layouts.app')

@section('sidebar')
<nav class="space-y-1 px-3 text-sm">

  <a href="{{ route('dashboard') }}"
     class="flex items-center gap-2 px-3 py-2 rounded-lg
            bg-indigo-100 text-indigo-700 font-semibold">
    🏠 Dashboard
  </a>

  <a href="{{ route('quiz.index') }}"
     class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100">
    ❓ Quiz
  </a>

  <a href="{{ route('puzzle.index') }}"
     class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100">
    🧩 Puzzle
  </a>

  <a href="{{ route('tts.menu') }}"
     class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100">
    🧠 Teka-Teki Silang
  </a>

  <a href="{{ route('surprise.index') }}"
     class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100">
    🎁 Hadiah
  </a>

</nav>
@endsection



@section('content')

<div class="mb-6">
  <h2 class="text-3xl font-extrabold text-gray-800">Dashboard</h2>
  <p class="text-gray-500 mt-1">
    Pilih permainan yang ingin kamu mainkan
  </p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

  <!-- QUIZ -->
  <a href="{{ route('quiz.index') }}"
     class="group bg-white rounded-xl p-6 shadow
            hover:shadow-xl transition hover:-translate-y-1">
    <div class="text-4xl mb-3">❓</div>
    <h3 class="font-bold text-lg">Quiz Rohani</h3>
    <p class="text-sm text-gray-500">Uji pengetahuan imanmu</p>
    <span class="text-indigo-600 font-semibold mt-4 inline-block">
      Mulai →
    </span>
  </a>

  <!-- TTS -->
  <a href="{{ route('tts.menu') }}"
     class="group bg-gradient-to-br from-indigo-500 to-blue-600
            text-white rounded-xl p-6 shadow-lg
            hover:shadow-2xl transition hover:-translate-y-1">
    <div class="text-4xl mb-3">🧠</div>
    <h3 class="font-bold text-lg">Teka-Teki Silang</h3>
    <p class="text-sm text-indigo-100">Single & Multiplayer</p>
    <span class="font-semibold mt-4 inline-block">
      Main →
    </span>
  </a>

</div>
@endsection
