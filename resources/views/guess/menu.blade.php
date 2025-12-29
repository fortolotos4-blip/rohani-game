@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-4">
  <h2 class="text-2xl mb-4 font-bold">Tebak Gambar</h2>

  <div class="grid grid-cols-2 gap-4">
    <div class="bg-white p-4 rounded shadow">
      <h3 class="font-semibold mb-2">Single</h3>
      <p class="text-sm text-gray-600">Main sendiri, jawab semua soal.</p>
      <div class="mt-4 text-right">
        <a href="{{ route('guess.single') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Main Single</a>
      </div>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h3 class="font-semibold mb-2">Duo</h3>
      <p class="text-sm text-gray-600">Main berpasangan, bergantian menjawab.</p>
      <div class="mt-4 text-right">
        <a href="{{ route('guess.duo') }}" class="px-4 py-2 bg-red-600 text-white rounded">Main Duo</a>
      </div>
    </div>
  </div>
</div>
@endsection
