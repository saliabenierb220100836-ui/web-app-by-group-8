@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
  <h1 class="mb-4 text-2xl font-extrabold text-[#0d3b66]">Dashboard</h1>

  <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ([
      ['PCs in use', $stats['in_use'], 'text-red-600'],
      ['PCs free', $stats['available'], 'text-emerald-600'],
      ['Revenue today', '₱'.number_format($stats['revenue_today'], 2), 'text-[#0d3b66]'],
      ['Sessions today', $stats['sessions_today'], 'text-[#0d3b66]'],
      ['Pending bookings', $stats['pending_bookings'], 'text-amber-600'],
      ['Payments to review', $stats['pending_payments'], 'text-amber-600'],
      ['Active members', $stats['members'], 'text-[#0d3b66]'],
      ['In maintenance', $stats['maintenance'], 'text-slate-500'],
    ] as [$label, $value, $color])
      <div class="rounded-xl bg-white p-4 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-black {{ $color }}">{{ $value }}</p>
      </div>
    @endforeach
  </div>

  <section class="mb-6 rounded-xl bg-white p-5 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
      <h2 class="font-extrabold text-[#0d3b66]">Floor map</h2>
      <p class="text-xs text-slate-500"><span class="mr-3"><i class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-500"></i>Free</span><span class="mr-3"><i class="mr-1 inline-block h-2 w-2 rounded-full bg-red-500"></i>In use</span><span><i class="mr-1 inline-block h-2 w-2 rounded-full bg-slate-400"></i>Maintenance</span></p>
    </div>

    @foreach (['standard' => 'Main floor', 'vip' => 'VIP room'] as $type => $label)
      <p class="mb-2 mt-3 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
      <div class="grid grid-cols-3 gap-2 sm:grid-cols-5 lg:grid-cols-10">
        @foreach ($computers->where('type', $type) as $pc)
          @php $c = ['available' => 'border-emerald-300 bg-emerald-50', 'in_use' => 'border-red-300 bg-red-50', 'maintenance' => 'border-slate-300 bg-slate-100'][$pc->status] ?? 'border-slate-300 bg-slate-100'; @endphp
          <div class="rounded-lg border p-2 text-center {{ $c }}">
            <p class="text-xs font-black text-[#0d3b66]">{{ $pc->name }}</p>
            <p class="truncate text-[11px] text-slate-600">{{ $pc->activeSession?->user?->name ?? ($pc->status === 'maintenance' ? 'Maintenance' : 'Free') }}</p>
          </div>
        @endforeach
      </div>
    @endforeach
    @if ($computers->isEmpty()) <p class="text-sm text-slate-500">No PCs yet. Run the seeder or add them under PCs.</p> @endif
  </section>

  <section class="overflow-x-auto rounded-xl bg-white shadow-sm">
    <div class="flex items-center justify-between px-5 pt-5">
      <h2 class="font-extrabold text-[#0d3b66]">Playing now</h2>
      <a href="{{ route('admin.attendance.index') }}" class="text-sm font-bold text-[#1e3a8a] hover:underline">Check in / out &rarr;</a>
    </div>
    <table class="mt-2 min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-2">PC</th><th class="px-5 py-2">Member</th><th class="px-5 py-2">Since</th><th class="px-5 py-2">Rate</th></tr></thead>
      <tbody class="divide-y">
        @forelse ($active as $s)
          <tr><td class="px-5 py-2 font-bold">{{ $s->computer->name }}</td><td class="px-5 py-2">{{ $s->user->name }}</td><td class="px-5 py-2">{{ $s->started_at->format('g:i A') }} <span class="text-slate-400">({{ $s->started_at->diffForHumans(null, true) }})</span></td><td class="px-5 py-2">₱{{ rtrim(rtrim(number_format($s->rate_applied, 2), '0'), '.') }}/hr @if ($s->promo_name)<span class="text-xs font-bold text-yellow-700">{{ $s->promo_name }}</span>@endif</td></tr>
        @empty
          <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">Nobody is checked in.</td></tr>
        @endforelse
      </tbody>
    </table>
  </section>
@endsection
