@extends('layouts.admin')
@section('title', 'Pricing')

@section('content')
  <h1 class="mb-4 text-2xl font-extrabold text-[#0d3b66]">Pricing &amp; rules</h1>

  <form method="POST" action="{{ route('admin.pricing.update') }}" class="max-w-2xl space-y-5 rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @method('PUT')

    <div class="grid gap-4 sm:grid-cols-3">
      @foreach ([['account_fee', 'Account creation (₱)'], ['rate_standard', 'Standard PC (₱/hr)'], ['rate_vip', 'VIP PC (₱/hr)']] as [$key, $label])
        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">{{ $label }}</label>
          <input type="number" step="0.01" min="0" name="{{ $key }}" required value="{{ old($key, $values[$key]) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-lg font-bold">
        </div>
      @endforeach
    </div>

    <div class="grid gap-4 border-t pt-5 sm:grid-cols-3">
      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">VIP advance booking (minutes)</label>
        <input type="number" min="0" name="vip_min_advance_minutes" required value="{{ old('vip_min_advance_minutes', $values['vip_min_advance_minutes']) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
        <p class="mt-1 text-xs text-slate-500">How far ahead VIP bookings must start.</p>
      </div>
      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Max booking length (hours)</label>
        <input type="number" min="1" max="24" name="booking_max_hours" required value="{{ old('booking_max_hours', $values['booking_max_hours']) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
      </div>
      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Billing block (minutes)</label>
        <input type="number" min="1" max="60" name="billing_increment_minutes" required value="{{ old('billing_increment_minutes', $values['billing_increment_minutes']) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
        <p class="mt-1 text-xs text-slate-500">Play time is rounded up to this. 15 = a 20 min session bills 30 min.</p>
      </div>
    </div>

    <button class="rounded-full bg-[#1e3a8a] px-6 py-2.5 font-bold text-white hover:bg-[#0d3b66]">Save pricing</button>
    <p class="text-xs text-slate-500">Night and student discounts are edited under <a href="{{ route('admin.promos.index') }}" class="font-bold text-[#1e3a8a] hover:underline">Promos</a>.</p>
  </form>
@endsection
