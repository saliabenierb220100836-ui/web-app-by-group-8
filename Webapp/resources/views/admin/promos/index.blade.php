@extends('layouts.admin')
@section('title', 'Promos')

@section('content')
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-extrabold text-[#0d3b66]">Promos</h1>
    <a href="{{ route('admin.promos.create') }}" class="rounded-full bg-[#1e3a8a] px-4 py-2 text-sm font-bold text-white hover:bg-[#0d3b66]">+ New promo</a>
  </div>

  <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Promo</th><th class="px-4 py-3">Discount</th><th class="px-4 py-3">Applies to</th><th class="px-4 py-3">Hours</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead>
      <tbody class="divide-y">
        @foreach ($promos as $p)
          <tr>
            <td class="px-4 py-3 font-bold">{{ $p->title }} @if ($p->requires_student) <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs text-sky-700">Students</span> @endif</td>
            <td class="px-4 py-3">{{ $p->discountLabel() }}</td>
            <td class="px-4 py-3">{{ ['all' => 'All PCs', 'standard' => 'Standard', 'vip' => 'VIP'][$p->applies_to] }}</td>
            <td class="px-4 py-3">{{ $p->startHm() && $p->endHm() ? $p->startHm().' - '.$p->endHm() : 'All day' }}</td>
            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $p->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $p->is_active ? 'Live' : 'Off' }}</span></td>
            <td class="px-4 py-3"><div class="flex gap-3 font-bold"><a href="{{ route('admin.promos.edit', $p) }}" class="text-[#1e3a8a] hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.promos.destroy', $p) }}" onsubmit="return confirm('Delete this promo?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form></div></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <p class="mt-3 text-xs text-slate-500">Promos never stack: each member gets the single lowest rate available to them. The promo page shows every active promo.</p>
@endsection
