@extends('layouts.admin')
@section('title', 'PCs')

@section('content')
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-extrabold text-[#0d3b66]">PCs <span class="text-base font-semibold text-slate-400">({{ $computers->where('type', 'standard')->count() }} standard, {{ $computers->where('type', 'vip')->count() }} VIP)</span></h1>
    <a href="{{ route('admin.computers.create') }}" class="rounded-full bg-[#1e3a8a] px-4 py-2 text-sm font-bold text-white hover:bg-[#0d3b66]">+ Add PC</a>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach ($computers as $pc)
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 {{ $pc->isVip() ? 'ring-yellow-400' : 'ring-slate-200' }}">
        <x-pc-image :computer="$pc" class="h-32 w-full" />
        <div class="p-3">
          <div class="flex items-center justify-between">
            <p class="font-extrabold text-[#0d3b66]">{{ $pc->name }}</p>
            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ ['available' => 'bg-emerald-100 text-emerald-700', 'in_use' => 'bg-red-100 text-red-700', 'maintenance' => 'bg-slate-200 text-slate-600'][$pc->status] }}">{{ str_replace('_', ' ', ucfirst($pc->status)) }}</span>
          </div>
          <p class="text-xs text-slate-500">{{ $pc->isVip() ? 'VIP room' : 'Standard' }}{{ $pc->image_path ? '' : ' · no photo yet' }}</p>
          <div class="mt-2 flex gap-3 text-sm font-bold">
            <a href="{{ route('admin.computers.edit', $pc) }}" class="text-[#1e3a8a] hover:underline">Edit / photo</a>
            <form method="POST" action="{{ route('admin.computers.destroy', $pc) }}" onsubmit="return confirm('Delete {{ $pc->name }}?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endsection
