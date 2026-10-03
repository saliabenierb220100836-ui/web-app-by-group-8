@extends('layouts.admin')
@section('title', $method->exists ? 'Edit '.$method->name : 'Add payment method')

@section('content')
  <h1 class="mb-4 text-2xl font-extrabold text-[#0d3b66]">{{ $method->exists ? 'Edit '.$method->name : 'Add payment method' }}</h1>

  <form method="POST" enctype="multipart/form-data" action="{{ $method->exists ? route('admin.payment-methods.update', $method) : route('admin.payment-methods.store') }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @if ($method->exists) @method('PUT') @endif

    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Name</label>
        <input name="name" required value="{{ old('name', $method->name) }}" placeholder="GCash, BDO, Cash..." class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Type</label>
        <select name="type" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
          @foreach (['cash' => 'Cash', 'ewallet' => 'E-wallet', 'bank' => 'Bank transfer'] as $v => $l) <option value="{{ $v }}" @selected(old('type', $method->type) === $v)>{{ $l }}</option> @endforeach
        </select></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Account name</label>
        <input name="account_name" value="{{ old('account_name', $method->account_name) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Account / mobile number</label>
        <input name="account_number" value="{{ old('account_number', $method->account_number) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 font-mono"></div>
    </div>

    <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Instructions for members</label>
      <textarea name="instructions" rows="3" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('instructions', $method->instructions) }}</textarea></div>

    <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">QR code (optional)</label>
      @if ($method->qrUrl()) <img src="{{ $method->qrUrl() }}" alt="" class="mb-2 h-32 rounded-lg border object-contain"> @endif
      <input type="file" name="qr" accept="image/png,image/jpeg,image/webp" class="text-sm"></div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $method->sort_order) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <label class="flex items-center gap-2 self-end pb-2 text-sm font-bold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active)) class="h-4 w-4"> Show to members</label>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-full bg-[#1e3a8a] px-6 py-2.5 font-bold text-white hover:bg-[#0d3b66]">Save</button>
      <a href="{{ route('admin.payment-methods.index') }}" class="text-sm font-semibold text-slate-500 hover:underline">Cancel</a>
    </div>
  </form>
@endsection
