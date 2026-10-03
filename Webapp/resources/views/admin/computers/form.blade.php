@extends('layouts.admin')
@section('title', $computer->exists ? 'Edit '.$computer->name : 'Add PC')

@section('content')
  <h1 class="mb-4 text-2xl font-extrabold text-[#0d3b66]">{{ $computer->exists ? 'Edit '.$computer->name : 'Add a PC' }}</h1>

  <form method="POST" enctype="multipart/form-data" action="{{ $computer->exists ? route('admin.computers.update', $computer) : route('admin.computers.store') }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @if ($computer->exists) @method('PUT') @endif

    <div>
      <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Name</label>
      <input name="name" required maxlength="50" value="{{ old('name', $computer->name) }}" placeholder="PC-01 or VIP-01" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Type</label>
        <select name="type" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
          <option value="standard" @selected(old('type', $computer->type) === 'standard')>Standard</option>
          <option value="vip" @selected(old('type', $computer->type) === 'vip')>VIP room</option>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Status</label>
        <select name="status" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
          @foreach (['available' => 'Available', 'maintenance' => 'Maintenance'] as $v => $l)
            <option value="{{ $v }}" @selected(old('status', $computer->status) === $v)>{{ $l }}</option>
          @endforeach
        </select>
        @if ($computer->status === 'in_use') <p class="mt-1 text-xs text-slate-500">Currently in use. Status resets automatically at check-out.</p> @endif
      </div>
    </div>

    <div>
      <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Specs (shown to members)</label>
      <textarea name="specs" rows="3" maxlength="1000" placeholder="Ryzen 5 / RTX 3060 / 165Hz" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('specs', $computer->specs) }}</textarea>
    </div>

    <div>
      <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Photo (shown in the member feed)</label>
      @if ($computer->imageUrl()) <img src="{{ $computer->imageUrl() }}" alt="" class="mb-2 h-32 rounded-lg object-cover"> @endif
      <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="text-sm">
      <p class="mt-1 text-xs text-slate-500">JPG, PNG or WebP, up to 3 MB.</p>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-full bg-[#1e3a8a] px-6 py-2.5 font-bold text-white hover:bg-[#0d3b66]">Save</button>
      <a href="{{ route('admin.computers.index') }}" class="text-sm font-semibold text-slate-500 hover:underline">Cancel</a>
    </div>
  </form>
@endsection
