@extends('layouts.member')
@section('title', 'My Bookings')

@section('content')
  <h1 class="mb-4 text-xl font-extrabold text-[#0d3b66]">My bookings</h1>

  <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
        <tr><th class="px-4 py-3">PC</th><th class="px-4 py-3">When</th><th class="px-4 py-3">Est.</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($bookings as $b)
          @php $color = ['pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-emerald-100 text-emerald-800', 'completed' => 'bg-sky-100 text-sky-800', 'rejected' => 'bg-red-100 text-red-800', 'cancelled' => 'bg-slate-200 text-slate-600'][$b->status]; @endphp
          <tr>
            <td class="px-4 py-3 font-bold text-[#0d3b66]">{{ $b->computer->name }}</td>
            <td class="px-4 py-3">{{ $b->start_at->format('M j, g:i A') }} &ndash; {{ $b->end_at->format('g:i A') }} <span class="text-slate-400">({{ $b->hours }}h)</span></td>
            <td class="px-4 py-3">₱{{ number_format($b->estimated_amount, 2) }}</td>
            <td class="px-4 py-3">
              <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $color }}">{{ ucfirst($b->status) }}</span>
              @if ($b->admin_note) <p class="mt-1 text-xs text-slate-500">{{ $b->admin_note }}</p> @endif
            </td>
            <td class="px-4 py-3 text-right">
              @if ($b->isOpen() && $b->end_at->isFuture())
                <form method="POST" action="{{ route('bookings.cancel', $b) }}" onsubmit="return confirm('Cancel this booking?')">
                  @csrf
                  <button class="text-xs font-bold text-red-600 hover:underline">Cancel</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No bookings yet. <a href="{{ route('dashboard') }}" class="font-bold text-[#1e3a8a] hover:underline">Pick a PC</a>.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $bookings->links() }}</div>
@endsection
