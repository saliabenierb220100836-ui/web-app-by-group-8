@extends('layouts.admin')
@section('title', 'Attendance')

@section('content')
  <h1 class="mb-4 text-2xl font-extrabold text-[#0d3b66]">PC &amp; member attendance</h1>

  <div class="mb-6 grid gap-4 lg:grid-cols-3">
    {{-- Check-in --}}
    <form method="POST" action="{{ route('admin.attendance.check-in') }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
      @csrf
      <h2 class="font-extrabold text-[#0d3b66]">Check a member in</h2>
      <select name="user_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">Member...</option>
        @foreach ($members as $m) <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->email }})</option> @endforeach
      </select>
      <select name="computer_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">Free PC...</option>
        @foreach ($freePcs as $pc) <option value="{{ $pc->id }}">{{ $pc->name }}{{ $pc->isVip() ? ' (VIP)' : '' }}</option> @endforeach
      </select>
      <button class="w-full rounded-full bg-[#1e3a8a] py-2 text-sm font-bold text-white hover:bg-[#0d3b66]">Start session</button>
    </form>

    {{-- Active sessions --}}
    <section class="rounded-xl bg-white p-5 shadow-sm lg:col-span-2">
      <h2 class="mb-3 font-extrabold text-[#0d3b66]">Playing now ({{ $active->count() }})</h2>
      <div class="space-y-3">
        @forelse ($active as $s)
          <form method="POST" action="{{ route('admin.attendance.check-out', $s) }}" class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm" onsubmit="return confirm('Check out {{ $s->user->name }} from {{ $s->computer->name }}?')">
            @csrf
            <div class="min-w-[9rem] flex-1">
              <p class="font-bold">{{ $s->computer->name }} &middot; {{ $s->user->name }}</p>
              <p class="text-xs text-slate-500">Since {{ $s->started_at->format('g:i A') }} &middot; ₱{{ rtrim(rtrim(number_format($s->rate_applied, 2), '0'), '.') }}/hr @if ($s->promo_name) &middot; {{ $s->promo_name }} @endif</p>
            </div>
            <label class="flex items-center gap-1 text-xs font-semibold"><input type="checkbox" name="mark_paid" value="1" checked> Paid now</label>
            <select name="payment_method_id" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
              @foreach ($methods as $m) <option value="{{ $m->id }}">{{ $m->name }}</option> @endforeach
            </select>
            <button class="rounded-full bg-red-600 px-4 py-1.5 text-xs font-bold text-white">Check out</button>
          </form>
        @empty
          <p class="py-4 text-center text-sm text-slate-500">Nobody is checked in.</p>
        @endforelse
      </div>
    </section>
  </div>

  {{-- Report filters --}}
  <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm">
    <div><label class="block text-xs font-bold uppercase text-slate-500">From</label><input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"></div>
    <div><label class="block text-xs font-bold uppercase text-slate-500">To</label><input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"></div>
    <div><label class="block text-xs font-bold uppercase text-slate-500">PC</label>
      <select name="computer_id" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"><option value="">All</option>@foreach ($allPcs as $pc) <option value="{{ $pc->id }}" @selected(request('computer_id') == $pc->id)>{{ $pc->name }}</option> @endforeach</select></div>
    <div><label class="block text-xs font-bold uppercase text-slate-500">Member</label>
      <select name="user_id" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"><option value="">All</option>@foreach ($members as $m) <option value="{{ $m->id }}" @selected(request('user_id') == $m->id)>{{ $m->name }}</option> @endforeach</select></div>
    <div><label class="block text-xs font-bold uppercase text-slate-500">View</label>
      <select name="group" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
        <option value="log" @selected($group === 'log')>Full log</option><option value="pc" @selected($group === 'pc')>By PC</option><option value="member" @selected($group === 'member')>By member</option>
      </select></div>
    <button class="rounded-full bg-[#1e3a8a] px-5 py-2 text-sm font-bold text-white">Apply</button>
  </form>

  <div class="mb-4 grid grid-cols-3 gap-3">
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-500">Sessions</p><p class="text-2xl font-black text-[#0d3b66]">{{ $totals->sessions }}</p></div>
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-500">Hours billed</p><p class="text-2xl font-black text-[#0d3b66]">{{ number_format($totals->minutes / 60, 1) }}</p></div>
    <div class="rounded-xl bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-500">Billed amount</p><p class="text-2xl font-black text-[#0d3b66]">₱{{ number_format($totals->revenue, 2) }}</p></div>
  </div>

  <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
    @if ($group === 'log')
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Member</th><th class="px-4 py-3">PC</th><th class="px-4 py-3">In</th><th class="px-4 py-3">Out</th><th class="px-4 py-3">Billed</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Paid</th></tr></thead>
        <tbody class="divide-y">
          @forelse ($rows as $s)
            <tr>
              <td class="px-4 py-2 font-bold">{{ $s->user->name }}</td><td class="px-4 py-2">{{ $s->computer->name }}</td>
              <td class="px-4 py-2">{{ $s->started_at->format('M j, g:i A') }}</td><td class="px-4 py-2">{{ $s->ended_at?->format('g:i A') ?? 'Playing' }}</td>
              <td class="px-4 py-2">{{ $s->billed_minutes ? $s->billed_minutes.' min' : '-' }}</td>
              <td class="px-4 py-2 font-bold">{{ $s->amount !== null ? '₱'.number_format($s->amount, 2) : '-' }}</td>
              <td class="px-4 py-2">@if ($s->ended_at)<span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $s->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($s->payment_status) }}</span>@endif</td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No sessions in this range.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="p-4">{{ $rows->links() }}</div>
    @else
      <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">{{ $group === 'pc' ? 'PC' : 'Member' }}</th><th class="px-4 py-3">Sessions</th><th class="px-4 py-3">Hours billed</th><th class="px-4 py-3">Amount</th></tr></thead>
        <tbody class="divide-y">
          @forelse ($rows as $r)
            <tr><td class="px-4 py-2 font-bold">{{ $group === 'pc' ? $r->computer->name : $r->user->name }}</td><td class="px-4 py-2">{{ $r->sessions }}</td><td class="px-4 py-2">{{ number_format($r->minutes / 60, 1) }}</td><td class="px-4 py-2 font-bold">₱{{ number_format($r->revenue, 2) }}</td></tr>
          @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No sessions in this range.</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif
  </div>
@endsection
