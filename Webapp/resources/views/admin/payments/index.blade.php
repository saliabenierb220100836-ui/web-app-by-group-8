@extends('layouts.admin')
@section('title', 'Payments')

@section('content')
  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-extrabold text-[#0d3b66]">Payments</h1>
    <div class="flex rounded-full bg-white p-1 text-sm font-semibold shadow-sm">
      @foreach (['pending' => 'To review', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected', 'all' => 'All'] as $k => $l)
        <a href="{{ route('admin.payments.index', ['status' => $k]) }}" class="rounded-full px-3 py-1 {{ $status === $k ? 'bg-[#1e3a8a] text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $l }}</a>
      @endforeach
    </div>
  </div>

  <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">When</th><th class="px-4 py-3">Member</th><th class="px-4 py-3">For</th><th class="px-4 py-3">Method / ref</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead>
      <tbody class="divide-y">
        @forelse ($payments as $p)
          <tr>
            <td class="px-4 py-3">{{ $p->created_at->format('M j, g:i A') }}</td>
            <td class="px-4 py-3 font-bold">{{ $p->user->name }}</td>
            <td class="px-4 py-3">{{ $p->purpose === 'account_fee' ? 'Account fee' : ($p->pcSession?->computer?->name ?? 'Session') }}</td>
            <td class="px-4 py-3">{{ $p->method_name }} @if ($p->reference_no) <span class="block font-mono text-xs text-slate-500">{{ $p->reference_no }}</span> @endif @if ($p->proof_path) <a href="{{ route('admin.payments.proof', $p) }}" target="_blank" class="text-xs font-bold text-[#1e3a8a] hover:underline">View proof</a> @endif</td>
            <td class="px-4 py-3 font-bold">₱{{ number_format($p->amount, 2) }}</td>
            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ ['pending' => 'bg-amber-100 text-amber-800', 'confirmed' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-red-100 text-red-800'][$p->status] }}">{{ ucfirst($p->status) }}</span></td>
            <td class="px-4 py-3">
              @if ($p->status === 'pending')
                <div class="flex flex-wrap gap-2">
                  <form method="POST" action="{{ route('admin.payments.confirm', $p) }}">@csrf<button class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">Confirm</button></form>
                  <form method="POST" action="{{ route('admin.payments.reject', $p) }}" class="flex gap-1">@csrf<input name="note" placeholder="Reason" class="w-28 rounded-full border border-slate-300 px-2 py-1 text-xs"><button class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">Reject</button></form>
                </div>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">Nothing here.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $payments->links() }}</div>
@endsection
