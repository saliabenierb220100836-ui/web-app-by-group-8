@extends('layouts.admin')
@section('title', 'Bookings')

@section('content')
  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-extrabold text-[#0d3b66]">Bookings</h1>
    <div class="flex rounded-full bg-white p-1 text-sm font-semibold shadow-sm">
      @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'completed' => 'Done', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled', 'all' => 'All'] as $k => $l)
        <a href="{{ route('admin.bookings.index', ['status' => $k]) }}" class="rounded-full px-3 py-1 {{ $status === $k ? 'bg-[#1e3a8a] text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $l }}</a>
      @endforeach
    </div>
  </div>

  <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Member</th><th class="px-4 py-3">PC</th><th class="px-4 py-3">When</th><th class="px-4 py-3">Est.</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
      <tbody class="divide-y">
        @forelse ($bookings as $b)
          <tr>
            <td class="px-4 py-3 font-bold">{{ $b->user->name }}</td>
            <td class="px-4 py-3">{{ $b->computer->name }} @if ($b->computer->isVip()) <span class="rounded-full bg-yellow-400 px-1.5 text-[10px] font-black">VIP</span> @endif</td>
            <td class="px-4 py-3">{{ $b->start_at->format('M j, g:i A') }} &ndash; {{ $b->end_at->format('g:i A') }}</td>
            <td class="px-4 py-3">₱{{ number_format($b->estimated_amount, 2) }}</td>
            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ ['pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-emerald-100 text-emerald-800', 'completed' => 'bg-sky-100 text-sky-800', 'rejected' => 'bg-red-100 text-red-800', 'cancelled' => 'bg-slate-200 text-slate-600'][$b->status] }}">{{ ucfirst($b->status) }}</span></td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap items-center gap-2">
                @if ($b->status === 'pending')
                  <form method="POST" action="{{ route('admin.bookings.approve', $b) }}">@csrf<button class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">Approve</button></form>
                @endif
                @if ($b->status === 'approved')
                  <form method="POST" action="{{ route('admin.bookings.check-in', $b) }}">@csrf<button class="rounded-full bg-[#1e3a8a] px-3 py-1 text-xs font-bold text-white">Check in now</button></form>
                @endif
                @if ($b->isOpen())
                  <form method="POST" action="{{ route('admin.bookings.reject', $b) }}" class="flex gap-1">@csrf
                    <input name="admin_note" placeholder="Reason (optional)" class="w-32 rounded-full border border-slate-300 px-2 py-1 text-xs">
                    <button class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">Reject</button></form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Nothing here.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $bookings->links() }}</div>
@endsection
