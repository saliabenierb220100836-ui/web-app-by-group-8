<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Coinnect')  - Coinnect</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
  @php
    $links = [
      ['dashboard', 'PCs', 'dashboard'],
      ['promos', 'Promos', 'promos'],
      ['chat.index', 'Chat', 'chat.*'],
      ['bookings.index', 'My Bookings', 'bookings.*'],
      ['payments.index', 'Payments', 'payments.*'],
      ['profile.edit', 'Profile', 'profile.*'],
    ];
  @endphp

  <header class="sticky top-0 z-30 bg-[#0d3b66] shadow-lg">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-yellow-400 text-sm font-black text-[#0d3b66]">C</span>
        <span class="text-lg font-extrabold tracking-wide text-white">COINNECT</span>
      </a>

      @auth
        <nav class="hidden items-center gap-1 md:flex">
          @foreach ($links as [$route, $label, $pattern])
            @continue(auth()->user()->isAdmin() && ! in_array($route, ['promos', 'profile.edit']))
            <a href="{{ route($route) }}" class="rounded-full px-3 py-1.5 text-sm font-semibold {{ request()->routeIs($pattern) ? 'bg-white text-[#0d3b66]' : 'text-blue-100 hover:bg-white/10' }}">{{ $label }}</a>
          @endforeach
          @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="rounded-full bg-yellow-400 px-3 py-1.5 text-sm font-bold text-[#0d3b66]">Admin Panel</a>
          @endif
        </nav>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="rounded-full border border-white/30 px-3 py-1.5 text-sm font-semibold text-white hover:bg-white/10">Log out</button>
        </form>
      @else
        <div class="flex items-center gap-2">
          <a href="{{ route('promos') }}" class="rounded-full px-3 py-1.5 text-sm font-semibold text-blue-100 hover:bg-white/10">Promos</a>
          <a href="{{ route('login') }}" class="rounded-full bg-yellow-400 px-4 py-1.5 text-sm font-bold text-[#0d3b66]">Log in</a>
        </div>
      @endauth
    </div>

    @auth
      {{-- Mobile nav --}}
      <nav class="flex gap-1 overflow-x-auto border-t border-white/10 px-3 py-2 md:hidden">
        @foreach ($links as [$route, $label, $pattern])
          @continue(auth()->user()->isAdmin() && ! in_array($route, ['promos', 'profile.edit']))
          <a href="{{ route($route) }}" class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ request()->routeIs($pattern) ? 'bg-white text-[#0d3b66]' : 'text-blue-100' }}">{{ $label }}</a>
        @endforeach
      </nav>
    @endauth
  </header>

  <main class="mx-auto max-w-7xl px-4 py-6">
    @include('partials.flash')
    @yield('content')
  </main>

  @stack('scripts')
</body>
</html>
