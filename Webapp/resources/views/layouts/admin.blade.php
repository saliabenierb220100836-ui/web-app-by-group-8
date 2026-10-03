<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title', 'Admin')  - Coinnect Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
  @php
    $pendingBookings = \App\Models\Booking::where('status', 'pending')->count();
    $pendingPayments = \App\Models\Payment::where('status', 'pending')->count();
    $nav = [
      ['admin.dashboard', 'Dashboard', 'admin.dashboard', null],
      ['admin.attendance.index', 'Attendance', 'admin.attendance.*', null],
      ['admin.bookings.index', 'Bookings', 'admin.bookings.*', $pendingBookings],
      ['admin.payments.index', 'Payments', 'admin.payments.*', $pendingPayments],
      ['admin.members.index', 'Members', 'admin.members.*', null],
      ['admin.computers.index', 'PCs', 'admin.computers.*', null],
      ['admin.pricing.edit', 'Pricing', 'admin.pricing.*', null],
      ['admin.promos.index', 'Promos', 'admin.promos.*', null],
      ['admin.payment-methods.index', 'Payment Methods', 'admin.payment-methods.*', null],
    ];
  @endphp

  <div class="flex min-h-screen flex-col md:flex-row">
    <aside class="bg-[#0d3b66] text-blue-100 md:w-60 md:shrink-0">
      <div class="flex items-center justify-between gap-2 px-4 py-4 md:block">
        <div class="flex items-center gap-2">
          <span class="flex h-9 w-9 items-center justify-center rounded-full bg-yellow-400 text-sm font-black text-[#0d3b66]">C</span>
          <div>
            <p class="font-extrabold leading-tight tracking-wide text-white">COINNECT</p>
            <p class="text-[11px] uppercase tracking-widest text-yellow-300">Admin</p>
          </div>
        </div>
      </div>

      <nav class="flex gap-1 overflow-x-auto px-3 pb-3 md:block md:space-y-1 md:overflow-visible">
        @foreach ($nav as [$route, $label, $pattern, $badge])
          <a href="{{ route($route) }}" class="flex items-center justify-between gap-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs($pattern) ? 'bg-white text-[#0d3b66]' : 'hover:bg-white/10' }}">
            <span>{{ $label }}</span>
            @if ($badge)
              <span class="rounded-full bg-red-500 px-2 text-xs font-bold text-white">{{ $badge }}</span>
            @endif
          </a>
        @endforeach
      </nav>

      <div class="hidden border-t border-white/10 p-4 text-xs md:block">
        <p class="truncate font-semibold text-white">{{ auth()->user()->name }}</p>
        <a href="{{ route('profile.edit') }}" class="mt-1 block hover:underline">Change my password</a>
        <a href="{{ route('promos') }}" class="block hover:underline">View member promo page</a>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
          @csrf
          <button class="rounded-full border border-white/30 px-3 py-1 font-semibold text-white hover:bg-white/10">Log out</button>
        </form>
      </div>
    </aside>

    <main class="min-w-0 flex-1 p-4 md:p-8">
      @include('partials.flash')
      @yield('content')
    </main>
  </div>

  @stack('scripts')
</body>
</html>
