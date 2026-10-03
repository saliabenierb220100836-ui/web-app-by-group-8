@extends('layouts.member')
@section('title', 'Choose your PC')

@section('content')
  {{-- Summary strip --}}
  <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl bg-white p-4 shadow-sm">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hello</p>
      <p class="mt-1 truncate text-lg font-extrabold text-[#0d3b66]">{{ auth()->user()->name }}</p>
      @if (auth()->user()->is_student)
        <span class="mt-1 inline-block rounded-full bg-sky-100 px-2 py-0.5 text-xs font-bold text-sky-700">Verified student</span>
      @endif
    </div>

    <div class="rounded-xl bg-white p-4 shadow-sm">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Right now</p>
      @if ($activeSession)
        <p class="mt-1 font-extrabold text-emerald-600">Playing on {{ $activeSession->computer->name }}</p>
        <p class="text-xs text-slate-500">Since {{ $activeSession->started_at->format('g:i A') }}</p>
      @elseif ($nextBooking)
        <p class="mt-1 font-extrabold text-[#0d3b66]">{{ $nextBooking->computer->name }}</p>
        <p class="text-xs text-slate-500">{{ ucfirst($nextBooking->status) }} &middot; {{ $nextBooking->start_at->format('M j, g:i A') }}</p>
      @else
        <p class="mt-1 font-semibold text-slate-600">No active session or booking</p>
      @endif
    </div>

    <div class="rounded-xl bg-white p-4 shadow-sm">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Amount due</p>
      <p class="mt-1 text-2xl font-extrabold {{ $amountDue > 0 ? 'text-red-600' : 'text-slate-700' }}">₱{{ number_format($amountDue, 2) }}</p>
      @if ($amountDue > 0)
        <a href="{{ route('payments.index') }}" class="text-xs font-bold text-[#1e3a8a] hover:underline">Pay now &rarr;</a>
      @endif
    </div>

    <div class="rounded-xl bg-white p-4 shadow-sm">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Free PCs</p>
      <p class="mt-1 text-lg font-extrabold text-[#0d3b66]">{{ $counts['standard'] }} standard &middot; {{ $counts['vip'] }} VIP</p>
    </div>
  </div>

  {{-- Promo chips --}}
  @if ($promos->isNotEmpty())
    <div class="mb-6 flex flex-wrap items-center gap-2">
      @foreach ($promos as $row)
        <a href="{{ route('promos') }}" class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $row['live'] ? 'border-yellow-400 bg-yellow-100 text-yellow-900' : 'border-slate-200 bg-white text-slate-500' }}">
          {{ $row['promo']->title }} &middot; {{ $row['promo']->discountLabel() }}
          @if ($row['live']) <span class="rounded-full bg-yellow-400 px-1.5 text-[10px] text-[#0d3b66]">LIVE NOW</span> @endif
        </a>
      @endforeach
    </div>
  @endif

  {{-- Filter tabs --}}
  <div class="mb-4 flex items-center justify-between gap-3">
    <h1 class="text-xl font-extrabold text-[#0d3b66]">Pick your PC</h1>
    <div class="flex rounded-full bg-white p-1 text-sm font-semibold shadow-sm">
      @foreach (['all' => 'All', 'standard' => 'Standard', 'vip' => 'VIP room'] as $key => $label)
        <a href="{{ route('dashboard', $key !== 'all' ? ['type' => $key] : []) }}" class="rounded-full px-4 py-1.5 {{ ($type ?? 'all') === $key ? 'bg-[#1e3a8a] text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
      @endforeach
    </div>
  </div>

  @if ($cards->isEmpty())
    <div class="rounded-xl bg-white p-10 text-center text-slate-500 shadow-sm">No PCs have been added yet. Please check back soon.</div>
  @endif

  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach ($cards as $card)
      @php
        $pc = $card['pc'];
        $state = $card['state'];
        $badge = [
          'available' => 'bg-emerald-500 text-white',
          'in_use' => 'bg-red-500 text-white',
          'reserved' => 'bg-amber-400 text-amber-950',
          'maintenance' => 'bg-slate-500 text-white',
        ][$state];
        $stateLabel = ['available' => 'Available', 'in_use' => 'In use', 'reserved' => 'Reserved', 'maintenance' => 'Maintenance'][$state];
      @endphp

      <article class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md {{ $pc->isVip() ? 'ring-yellow-400' : '' }}">
        <div class="relative">
          <x-pc-image :computer="$pc" class="h-40 w-full" />
          <span class="absolute left-2 top-2 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badge }}">{{ $stateLabel }}</span>
          @if ($pc->isVip())
            <span class="absolute right-2 top-2 rounded-full bg-yellow-400 px-2.5 py-0.5 text-xs font-black text-[#0d3b66]">VIP</span>
          @endif
        </div>

        <div class="p-4">
          <div class="flex items-baseline justify-between gap-2">
            <h3 class="font-extrabold text-[#0d3b66]">{{ $pc->name }}</h3>
            <p class="text-right">
              @if ($card['price']['promo'])
                <span class="text-xs text-slate-400 line-through">₱{{ rtrim(rtrim(number_format($card['price']['base'], 2), '0'), '.') }}</span>
              @endif
              <span class="text-lg font-black text-slate-800">₱{{ rtrim(rtrim(number_format($card['price']['rate'], 2), '0'), '.') }}</span><span class="text-xs text-slate-500">/hr</span>
            </p>
          </div>

          @if ($card['price']['promo'])
            <p class="mt-0.5 text-xs font-bold text-yellow-700">{{ $card['price']['promo']->title }} applied</p>
          @endif

          @if ($pc->specs)
            <p class="mt-2 line-clamp-2 text-xs text-slate-500">{{ $pc->specs }}</p>
          @endif

          @if ($card['next_booking'])
            <p class="mt-2 text-xs text-amber-700">Next booked: {{ $card['next_booking']->start_at->format('M j, g:i A') }}</p>
          @endif

          @if ($state === 'maintenance')
            <span class="mt-3 block rounded-lg bg-slate-100 py-2 text-center text-sm font-bold text-slate-400">Unavailable</span>
          @else
            <a href="{{ route('bookings.create', $pc) }}" class="mt-3 block rounded-lg bg-[#1e3a8a] py-2 text-center text-sm font-bold text-white hover:bg-[#0d3b66]">
              {{ $pc->isVip() ? 'Book ahead' : ($state === 'available' ? 'Reserve this PC' : 'Book a later slot') }}
            </a>
          @endif
        </div>
      </article>
    @endforeach
  </div>
@endsection
