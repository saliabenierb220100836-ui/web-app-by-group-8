@extends('layouts.member')
@section('title', 'Book '.$computer->name)

@section('content')
  <div class="mx-auto grid max-w-4xl gap-6 md:grid-cols-5">
    <div class="overflow-hidden rounded-xl bg-white shadow-sm md:col-span-2">
      <x-pc-image :computer="$computer" class="h-56 w-full" />
      <div class="p-4">
        <h1 class="text-xl font-extrabold text-[#0d3b66]">{{ $computer->name }}
          @if ($computer->isVip()) <span class="ml-1 rounded-full bg-yellow-400 px-2 py-0.5 align-middle text-xs font-black">VIP</span> @endif
        </h1>
        <p class="mt-1 text-2xl font-black">₱{{ rtrim(rtrim(number_format($price['rate'], 2), '0'), '.') }}<span class="text-sm font-medium text-slate-500">/hour</span></p>
        @if ($price['promo']) <p class="text-xs font-bold text-yellow-700">{{ $price['promo']->title }} is active for you right now</p> @endif
        @if ($computer->specs) <p class="mt-3 text-sm text-slate-600">{{ $computer->specs }}</p> @endif

        @if ($upcoming->isNotEmpty())
          <div class="mt-4 border-t pt-3">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Already booked</p>
            <ul class="mt-1 space-y-1 text-sm text-slate-600">
              @foreach ($upcoming as $b)
                <li>{{ $b->start_at->format('M j, g:i A') }} &ndash; {{ $b->end_at->format('g:i A') }}</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>
    </div>

    <form method="POST" action="{{ route('bookings.store', $computer) }}" class="space-y-4 rounded-xl bg-white p-6 shadow-sm md:col-span-3">
      @csrf
      <h2 class="text-lg font-extrabold text-[#0d3b66]">Reserve a slot</h2>

      @if ($computer->isVip())
        <p class="rounded-lg bg-yellow-50 px-3 py-2 text-sm text-yellow-900">VIP PCs need advance booking: pick a start time at least <strong>{{ $minAdvance }} minutes</strong> from now.</p>
      @endif

      <div>
        <label for="start_at" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Start time</label>
        <input type="datetime-local" id="start_at" name="start_at" required value="{{ old('start_at', now()->addMinutes(max($minAdvance, 15))->format('Y-m-d\TH:i')) }}"
               min="{{ now()->addMinutes($minAdvance)->format('Y-m-d\TH:i') }}" max="{{ now()->addDays(14)->format('Y-m-d\TH:i') }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-[#1e3a8a] focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30">
      </div>

      <div>
        <label for="hours" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">How many hours?</label>
        <select id="hours" name="hours" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-[#1e3a8a] focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30">
          @for ($h = 1; $h <= $maxHours; $h++)
            <option value="{{ $h }}" @selected((int) old('hours', 1) === $h)>{{ $h }} hour{{ $h > 1 ? 's' : '' }}  (about ₱{{ number_format($price['rate'] * $h, 2) }})</option>
          @endfor
        </select>
        <p class="mt-1 text-xs text-slate-500">Estimate only. You are billed for the actual time played, rounded up in small blocks, when you check out.</p>
      </div>

      <button class="w-full rounded-full bg-[#1e3a8a] py-3 font-bold text-white shadow hover:bg-[#0d3b66]">Send booking request</button>
      <a href="{{ route('dashboard') }}" class="block text-center text-sm font-semibold text-slate-500 hover:underline">Back to PCs</a>
    </form>
  </div>
@endsection
