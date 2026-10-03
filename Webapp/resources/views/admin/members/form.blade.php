@extends('layouts.admin')
@section('title', $member->exists ? 'Edit member' : 'New member')

@section('content')
  <h1 class="mb-4 text-2xl font-extrabold text-[#0d3b66]">{{ $member->exists ? 'Edit '.$member->name : 'Create member account' }}</h1>

  <form method="POST" action="{{ $member->exists ? route('admin.members.update', $member) : route('admin.members.store') }}" class="max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @if ($member->exists) @method('PUT') @endif

    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Full name</label>
        <input name="name" required value="{{ old('name', $member->name) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Email (login)</label>
        <input type="email" name="email" required value="{{ old('email', $member->email) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone</label>
        <input name="phone" value="{{ old('phone', $member->phone) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">{{ $member->exists ? 'New password (leave blank to keep)' : 'Password (blank = auto-generate)' }}</label>
        <input type="text" name="password" autocomplete="off" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 font-mono"></div>
    </div>

    <div class="grid gap-4 rounded-lg bg-sky-50 p-4 sm:grid-cols-2">
      <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Student ID no.</label>
        <input name="student_id_no" value="{{ old('student_id_no', $member->student_id_no) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5"></div>
      <label class="flex items-center gap-2 self-end pb-2 text-sm font-bold text-sky-900">
        <input type="checkbox" name="is_student" value="1" @checked(old('is_student', $member->is_student)) class="h-4 w-4"> ID checked: eligible for student promo
      </label>
    </div>

    @if ($member->exists)
      <label class="flex items-center gap-2 text-sm font-bold">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->is_active)) class="h-4 w-4"> Account active (untick to block login)
      </label>
    @else
      <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
        <label class="flex items-center gap-2 text-sm font-bold text-yellow-900">
          <input type="checkbox" name="collect_fee" value="1" checked class="h-4 w-4"> Collect account fee of ₱{{ number_format($accountFee, 2) }}
        </label>
        <div class="mt-3">
          <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Paid via</label>
          <select name="payment_method_id" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 sm:w-64">
            @foreach ($methods as $m) <option value="{{ $m->id }}">{{ $m->name }}</option> @endforeach
          </select>
          @if ($methods->isEmpty()) <p class="mt-1 text-xs text-slate-500">No active payment methods, so it will be logged as Cash.</p> @endif
        </div>
      </div>
    @endif

    <div class="flex items-center gap-3">
      <button class="rounded-full bg-[#1e3a8a] px-6 py-2.5 font-bold text-white hover:bg-[#0d3b66]">{{ $member->exists ? 'Save' : 'Create account' }}</button>
      <a href="{{ route('admin.members.index') }}" class="text-sm font-semibold text-slate-500 hover:underline">Cancel</a>
    </div>
  </form>
@endsection
