@extends('layouts.member')
@section('title', 'Payments')

@section('content')
  <h1 class="mb-4 text-xl font-extrabold text-[#0d3b66]">Payments</h1>

  <div class="grid gap-6 lg:grid-cols-5">
    <div class="space-y-6 lg:col-span-3">
      <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="mb-3 font-extrabold text-[#0d3b66]">Bills to pay</h2>

        @forelse ($unpaid as $s)
          @php $pending = $s->payments->firstWhere('status', 'pending'); @endphp
          <div class="mb-4 rounded-lg border border-slate-200 p-4">
            <div class="flex items-center justify-between gap-2">
              <div>
                <p class="font-bold">{{ $s->computer->name }} &middot; {{ $s->started_at->format('M j, g:i A') }}</p>
                <p class="text-xs text-slate-500">{{ $s->billed_minutes }} min billed at ₱{{ rtrim(rtrim(number_format($s->rate_applied, 2), '0'), '.') }}/hr @if ($s->promo_name) ({{ $s->promo_name }}) @endif</p>
              </div>
              <p class="text-xl font-black text-red-600">₱{{ number_format($s->amount, 2) }}</p>
            </div>

            @if ($pending)
              <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">Waiting for the front desk to confirm your {{ $pending->method_name }} payment.</p>
            @elseif ($methods->isEmpty())
              <p class="mt-3 text-sm text-slate-500">Please pay at the counter.</p>
            @else
              <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="mt-3 grid gap-3 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="pc_session_id" value="{{ $s->id }}">
                <select name="payment_method_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                  @foreach ($methods as $m) <option value="{{ $m->id }}">{{ $m->name }} ({{ ['cash' => 'Cash', 'ewallet' => 'E-wallet', 'bank' => 'Bank'][$m->type] }})</option> @endforeach
                </select>
                <input type="text" name="reference_no" maxlength="60" placeholder="Reference no. (not needed for cash)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="file" name="proof" accept="image/*" class="text-xs sm:col-span-2">
                <button class="rounded-full bg-[#1e3a8a] py-2 text-sm font-bold text-white hover:bg-[#0d3b66] sm:col-span-2">Submit payment</button>
              </form>
            @endif
          </div>
        @empty
          <p class="py-6 text-center text-sm text-slate-500">You're all paid up. 🎮</p>
        @endforelse
      </section>

      <section class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <h2 class="px-5 pt-5 font-extrabold text-[#0d3b66]">History</h2>
        <table class="mt-2 min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-2">Date</th><th class="px-4 py-2">For</th><th class="px-4 py-2">Method</th><th class="px-4 py-2">Amount</th><th class="px-4 py-2">Status</th></tr></thead>
          <tbody class="divide-y">
            @forelse ($history as $p)
              <tr>
                <td class="px-4 py-2">{{ $p->created_at->format('M j, g:i A') }}</td>
                <td class="px-4 py-2">{{ $p->purpose === 'account_fee' ? 'Account fee' : ($p->pcSession?->computer?->name ?? 'Session') }}</td>
                <td class="px-4 py-2">{{ $p->method_name }}</td>
                <td class="px-4 py-2 font-bold">₱{{ number_format($p->amount, 2) }}</td>
                <td class="px-4 py-2"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ ['pending' => 'bg-amber-100 text-amber-800', 'confirmed' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-red-100 text-red-800'][$p->status] }}">{{ ucfirst($p->status) }}</span>@if ($p->status === 'rejected' && $p->note) <span class="block text-xs text-slate-500">{{ $p->note }}</span> @endif</td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No payments yet.</td></tr>
            @endforelse
          </tbody>
        </table>
        <div class="px-4 pb-4">{{ $history->links() }}</div>
      </section>
    </div>

    <aside class="space-y-3 lg:col-span-2">
      <h2 class="font-extrabold text-[#0d3b66]">How to pay</h2>
      @forelse ($methods as $m)
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <p class="font-extrabold">{{ $m->name }}</p>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ ['cash' => 'Cash', 'ewallet' => 'E-wallet', 'bank' => 'Bank'][$m->type] }}</span>
          </div>
          @if ($m->account_name) <p class="mt-1 text-sm text-slate-600">Account name: <strong>{{ $m->account_name }}</strong></p> @endif
          @if ($m->account_number) <p class="text-sm text-slate-600">Number: <strong class="font-mono">{{ $m->account_number }}</strong></p> @endif
          @if ($m->instructions) <p class="mt-1 text-xs text-slate-500">{{ $m->instructions }}</p> @endif
          @if ($m->qrUrl()) <img src="{{ $m->qrUrl() }}" alt="{{ $m->name }} QR" class="mt-2 h-40 w-40 rounded-lg border object-contain"> @endif
        </div>
      @empty
        <div class="rounded-xl bg-white p-4 text-sm text-slate-500 shadow-sm">Payment options will be listed here. Ask the front desk for now.</div>
      @endforelse
      <p class="text-xs text-slate-500">New account fee: ₱{{ number_format($accountFee, 2) }}, paid once at the counter when staff create your account.</p>
    </aside>
  </div>
@endsection
