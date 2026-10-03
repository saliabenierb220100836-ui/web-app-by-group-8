@extends('layouts.member')
@section('title', 'Profile')

@section('content')
  <h1 class="mb-4 text-xl font-extrabold text-[#0d3b66]">My profile</h1>

  @if (session('status') === 'password-updated')
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">Password changed.</div>
  @endif

  <div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4 rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
      @csrf
      @method('PATCH')

      <div class="flex items-center gap-4">
        @if ($user->avatarUrl())
          <img src="{{ $user->avatarUrl() }}" alt="" class="h-20 w-20 rounded-full object-cover ring-2 ring-yellow-400">
        @else
          <span class="flex h-20 w-20 items-center justify-center rounded-full bg-[#1e3a8a] text-2xl font-black text-white ring-2 ring-yellow-400">{{ $user->initials() }}</span>
        @endif
        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Profile photo</label>
          <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="text-xs">
          @error('avatar') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label for="name" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Name</label>
          <input id="name" name="name" required value="{{ old('name', $user->name) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
          @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Email (login)</label>
          <input value="{{ $user->email }}" disabled class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-500">
        </div>
        <div>
          <label for="phone" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone</label>
          <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
          @error('phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        @unless ($user->isAdmin())
          <div>
            <label for="student_id_no" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Student ID no.</label>
            <input id="student_id_no" name="student_id_no" value="{{ old('student_id_no', $user->student_id_no) }}" class="w-full rounded-lg border border-slate-300 px-4 py-2.5">
            <p class="mt-1 text-xs {{ $user->is_student ? 'font-bold text-emerald-600' : 'text-slate-500' }}">
              {{ $user->is_student ? 'Student promo verified ✔' : 'Show your ID at the front desk to unlock the student promo.' }}
            </p>
          </div>
        @endunless
      </div>

      <button class="rounded-full bg-[#1e3a8a] px-6 py-2.5 font-bold text-white hover:bg-[#0d3b66]">Save changes</button>
    </form>

    <div class="space-y-6">
      <form method="POST" action="{{ route('password.update') }}" class="space-y-3 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <h2 class="font-extrabold text-[#0d3b66]">Change password</h2>
        <input type="password" name="current_password" placeholder="Current password" autocomplete="current-password" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm">
        <input type="password" name="password" placeholder="New password" autocomplete="new-password" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm">
        <input type="password" name="password_confirmation" placeholder="Confirm new password" autocomplete="new-password" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm">
        @foreach ($errors->updatePassword->all() as $error) <p class="text-xs text-red-600">{{ $error }}</p> @endforeach
        <button class="w-full rounded-full border-2 border-[#1e3a8a] py-2 text-sm font-bold text-[#1e3a8a] hover:bg-[#1e3a8a] hover:text-white">Update password</button>
      </form>

      @unless ($user->isAdmin())
        <div class="rounded-xl bg-white p-6 shadow-sm">
          <h2 class="mb-2 font-extrabold text-[#0d3b66]">Recent sessions</h2>
          @forelse ($recentSessions as $s)
            <div class="flex items-center justify-between border-b py-2 text-sm last:border-0">
              <span><strong>{{ $s->computer->name }}</strong> <span class="text-slate-500">{{ $s->started_at->format('M j, g:i A') }}</span></span>
              <span class="font-bold">{{ $s->amount !== null ? '₱'.number_format($s->amount, 2) : 'Playing' }}</span>
            </div>
          @empty
            <p class="text-sm text-slate-500">No sessions yet.</p>
          @endforelse
        </div>
      @endunless
    </div>
  </div>
@endsection
