@extends('layouts.admin')
@section('title', $promo->exists ? 'Edit promo' : 'New promo')

@section('content')
  <h1 class="mb-4 text-2xl font-extrabold text-[#0d3b66]">{{ $promo->exists ? 'Edit '.$promo->title : 'New promo' }}</h1>

  <form method="POST" enctype="multipart/form-data" action="{{ $promo->exists ? route('admin.promos.update', $promo) : route('admin.promos.store') }}" class="max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @if ($promo->exists) @method('PUT') @endif

    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Title</label>
        <input name="title" required value="{{ old('title', $promo->title) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Badge (e.g. HOT, NEW)</label>
        <input name="badge_label" maxlength="40" value="{{ old('badge_label', $promo->badge_label) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
    </div>

    <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Tagline</label>
      <input name="tagline" maxlength="160" value="{{ old('tagline', $promo->tagline) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
    <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Description</label>
      <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('description', $promo->description) }}</textarea></div>

    <div class="grid gap-4 rounded-lg bg-slate-50 p-4 sm:grid-cols-3">
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Discount type</label>
        <select name="discount_type" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
          @foreach (['percent' => '% off the rate', 'fixed_rate' => 'Fixed ₱ per hour', 'none' => 'Display only (no discount)'] as $v => $l) <option value="{{ $v }}" @selected(old('discount_type', $promo->discount_type) === $v)>{{ $l }}</option> @endforeach
        </select></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Value</label>
        <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', $promo->discount_value) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Applies to</label>
        <select name="applies_to" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
          @foreach (['all' => 'All PCs', 'standard' => 'Standard only', 'vip' => 'VIP only'] as $v => $l) <option value="{{ $v }}" @selected(old('applies_to', $promo->applies_to) === $v)>{{ $l }}</option> @endforeach
        </select></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Starts at</label>
        <input type="time" name="start_time" value="{{ old('start_time', $promo->startHm()) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Ends at</label>
        <input type="time" name="end_time" value="{{ old('end_time', $promo->endHm()) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></div>
      <p class="self-end text-xs text-slate-500">Leave both times empty for all day. Overnight works: 22:00 to 06:00.</p>
    </div>

    <div class="flex flex-wrap gap-6">
      <label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="requires_student" value="1" @checked(old('requires_student', $promo->requires_student)) class="h-4 w-4"> Students only (verified members)</label>
      <label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promo->is_active)) class="h-4 w-4"> Active</label>
    </div>

    <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Terms &amp; conditions</label>
      <textarea name="terms" rows="2" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('terms', $promo->terms) }}</textarea></div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Banner image</label>
        @if ($promo->imageUrl()) <img src="{{ $promo->imageUrl() }}" alt="" class="mb-2 h-24 rounded-lg object-cover"> @endif
        <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="text-sm"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $promo->sort_order) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-full bg-[#1e3a8a] px-6 py-2.5 font-bold text-white hover:bg-[#0d3b66]">Save promo</button>
      <a href="{{ route('admin.promos.index') }}" class="text-sm font-semibold text-slate-500 hover:underline">Cancel</a>
    </div>
  </form>
@endsection
