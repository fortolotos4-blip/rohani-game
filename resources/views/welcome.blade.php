@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center
            bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-800">

  <div class="bg-white/10 backdrop-blur-md
              border border-white/20
              rounded-2xl shadow-2xl
              p-10 max-w-md w-full text-center">

    <!-- ICON / LOGO -->
    <div class="mb-6">
      <span class="text-6xl">🧩</span>
    </div>

    <!-- TITLE -->
    <h1 class="text-4xl font-extrabold text-white mb-3 tracking-wide">
      Game Rohani
    </h1>

    <p class="text-gray-300 mb-8 text-sm leading-relaxed">
      Asah pengetahuan imanmu melalui permainan teka-teki silang rohani
      yang menantang dan menyenangkan.
    </p>

    <!-- BUTTON -->
    <a href="{{ route('dashboard') }}"
       class="inline-block w-full py-3 rounded-xl
              bg-gradient-to-r from-blue-500 to-indigo-600
              text-white font-semibold tracking-wide
              shadow-lg shadow-indigo-900/40
              hover:from-blue-600 hover:to-indigo-700
              hover:scale-[1.02] active:scale-[0.98]
              transition transform duration-200">

      ▶ Mulai Bermain
    </a>

    <!-- FOOTER -->
    <p class="mt-6 text-xs text-gray-400">
      © {{ date('Y') }} Game Rohani • All Rights Reserved
    </p>

  </div>
</div>
@endsection
