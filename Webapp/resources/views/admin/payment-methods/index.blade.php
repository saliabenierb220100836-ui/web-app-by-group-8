@extends('layouts.admin')
@section('title', 'Payment Methods')

@section('content')
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-extrabold text-[#0d3b66]">Payment methods</h1>
    <a href="{{ route('admin.payment-methods.create') }}" class="rounded-full bg-[#1e3a8a] px-4 py-2 text-sm font-bold text-white hover:bg-[#0d3b66]">+ Add method</a>
  </div>

  <p class="mb-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900">Only <strong>active</strong> methods are shown to members. Enter your real account name and number before activating an e-wallet or bank, because members will send money to exactly what is listed here.</p>

  <div class="grid gap-4 md:grid-cols-2">
    @foreach ($methods as $m)
      <div class="rounded-xl bg-white p-4 shadow-sm {{ $m->is_active ? '' : 'opacity-60' }}">
        <div class="flex items-center justify-between">
          <p class="font-extrabold text-[#0d3b66]">{{ $m->name }}</p>
          <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $m->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $m->is_active ? 'Active' : 'Hidden' }}</span>
        </div>
        <p class="text-xs uppercase tracking-wider text-slate-500">{{ ['cash' => 'Cash', 'ewallet' => 'E-wallet', 'bank' => 'Bank'][$m->type] }}</p>
        @if ($m->account_name || $m->account_number) <p class="mt-1 text-sm">{{ $m->account_name }} <span class="font-mono">{{ $m->account_number }}</span></p> @endif
        <div class="mt-2 flex gap-3 text-sm font-bold">
          <a href="{{ route('admin.payment-methods.edit', $m) }}" class="text-[#1e3a8a] hover:underline">Edit</a>
          <form method="POST" action="{{ route('admin.payment-methods.destroy', $m) }}" onsubmit="return confirm('Delete {{ $m->name }}?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
        </div>
      </div>
    @endforeach
  </div>
@endsection
