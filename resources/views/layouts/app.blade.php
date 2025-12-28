<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Game Rohani</title>

  @vite('resources/css/app.css')
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>

<body class="bg-gray-100" x-cloak>

<div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

  <!-- OVERLAY MOBILE -->
  <div
    x-show="sidebarOpen"
    @click="sidebarOpen=false"
    class="fixed inset-0 bg-black/40 z-30 md:hidden">
  </div>

  <!-- SIDEBAR -->
  <aside
    class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-white border-r
           transform transition-transform duration-200
           md:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <div class="p-4 text-lg font-extrabold text-indigo-600">
      🎮 Game Rohani
    </div>

    @yield('sidebar')
  </aside>

  <!-- CONTENT -->
  <main class="flex-1 md:ml-64">

    <!-- HEADER MOBILE -->
    <div class="md:hidden flex items-center justify-between p-4 bg-white shadow">
      <button @click="sidebarOpen=true" class="text-2xl">☰</button>
      <span class="font-semibold">Game Rohani</span>
    </div>

    <div class="p-6">
      @yield('content')
    </div>

  </main>
</div>

@stack('scripts')
</body>
</html>
