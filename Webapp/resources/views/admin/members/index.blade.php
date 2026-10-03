@extends('layouts.admin')
@section('title', 'Members')

@section('content')
  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-extrabold text-[#0d3b66]">Members</h1>
    <div class="flex gap-2">
      <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $search }}" placeholder="Search name, email, phone" class="rounded-full border border-slate-300 px-4 py-2 text-sm">
        <button class="rounded-full border border-slate-300 bg-white px-4 text-sm font-bold">Search</button>
      </form>
      <a href="{{ route('admin.members.create') }}" class="rounded-full bg-[#1e3a8a] px-4 py-2 text-sm font-bold text-white hover:bg-[#0d3b66]">+ New member</a>
    </div>
  </div>

  <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Phone</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Last login</th><th class="px-4 py-3"></th></tr></thead>
      <tbody class="divide-y">
        @forelse ($members as $m)
          <tr>
            <td class="px-4 py-3 font-bold">{{ $m->name }} @if ($m->is_student) <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs text-sky-700">Student</span> @endif</td>
            <td class="px-4 py-3">{{ $m->email }}</td>
            <td class="px-4 py-3">{{ $m->phone ?: '-' }}</td>
            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $m->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $m->is_active ? 'Active' : 'Deactivated' }}</span></td>
            <td class="px-4 py-3 text-slate-500">{{ $m->last_login_at?->format('M j, g:i A') ?? 'Never' }}</td>
            <td class="px-4 py-3 text-right"><a href="{{ route('admin.members.edit', $m) }}" class="font-bold text-[#1e3a8a] hover:underline">Edit</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No members found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $members->links() }}</div>
@endsection
