@extends('layouts.member')
@section('title', 'Promos')

@section('content')
  <div class="mb-8 rounded-2xl bg-gradient-to-r from-[#0d3b66] to-[#1e3a8a] p-8 text-center text-white shadow">
    <p class="text-sm font-bold uppercase tracking-widest text-yellow-300">Coinnect Gaming Hub</p>
    <h1 class="mt-1 text-3xl font-black">Promos &amp; Deals</h1>
    <p class="mt-1 text-blue-100">Play more, pay less.</p>
  </div>

  @if ($promos->isEmpty())
    <div class="rounded-xl bg-white p-10 text-center text-slate-500 shadow-sm">No promos right now. Check back soon!</div>
  @endif

  <div class="grid gap-6 md:grid-cols-2">
    @foreach ($promos as $row)
      @php $promo = $row['promo']; @endphp
      <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 {{ $row['live'] ? 'ring-yellow-400' : 'ring-slate-200' }}">
        @if ($promo->imageUrl())
          <img src="{{ $promo->imageUrl() }}" alt="{{ $promo->title }}" class="h-48 w-full object-cover" loading="lazy">
        @else
          <div class="flex h-32 items-center justify-center bg-gradient-to-br from-[#0d3b66] to-slate-900 text-5xl">{{ $promo->requires_student ? '🎓' : '🌙' }}</div>
        @endif

        <div class="p-6">
          <div class="flex flex-wrap items-center gap-2">
            @if ($promo->badge_label) <span class="rounded-full bg-yellow-400 px-2.5 py-0.5 text-xs font-black text-[#0d3b66]">{{ $promo->badge_label }}</span> @endif
            @if ($row['live']) <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">Live now</span> @endif
            @if ($promo->requires_student) <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-700">Students only</span> @endif
          </div>

          <h2 class="mt-2 text-2xl font-black text-[#0d3b66]">{{ $promo->title }}</h2>
          @if ($promo->tagline) <p class="text-sm font-semibold text-slate-500">{{ $promo->tagline }}</p> @endif

          <p class="mt-3 text-3xl font-black text-slate-800">{{ $promo->discountLabel() }}</p>

          <p class="mt-1 text-sm text-slate-500">
            {{ $promo->applies_to === 'all' ? 'All PCs' : ($promo->applies_to === 'vip' ? 'VIP PCs only' : 'Standard PCs only') }}
            @if ($promo->startHm() && $promo->endHm())
              &middot; {{ \Carbon\Carbon::createFromFormat('H:i', $promo->startHm())->format('g:i A') }} to {{ \Carbon\Carbon::createFromFormat('H:i', $promo->endHm())->format('g:i A') }}
            @else
              &middot; All day
            @endif
          </p>

          @if ($promo->description) <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $promo->description }}</p> @endif
          @if ($promo->terms) <p class="mt-3 whitespace-pre-line border-t pt-3 text-xs text-slate-400">{{ $promo->terms }}</p> @endif
        </div>
      </article>
    @endforeach
  </div>

  <p class="mt-6 text-center text-xs text-slate-500">Promos don't stack. The lowest rate available to you is applied automatically.</p>
@endsection
