<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Game Rohani</title>
  <!-------- Alpine --------->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <!-------- Tailwind ------->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
  [x-cloak] { display: none !important; }
  </style>

</head>

<body class="bg-gray-100 min-h-screen overflow-x-hidden">
  <div class="flex w-full overflow-x-hidden">
    @if(View::hasSection('sidebar'))
      <aside class="w-60 bg-white border-r p-4 min-h-screen overflow-x-hidden">
        <h3 class="font-bold mb-4">Menu</h3>
        @yield('sidebar')
      </aside>
    @endif

    <main class="flex-1 p-3 sm:p-6 overflow-x-hidden">
      @yield('content')
    </main>
  </div>
</body>
</html>