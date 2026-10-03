@extends('layouts.member')
@section('title', 'Chat')

@section('content')
  <h1 class="mb-4 text-xl font-extrabold text-[#0d3b66]">Player chat</h1>

  <div class="grid h-[70vh] min-h-[420px] overflow-hidden rounded-xl bg-white shadow-sm md:grid-cols-4">
    <aside class="overflow-y-auto border-b bg-slate-50 md:border-b-0 md:border-r">
      <button data-with="lobby" class="chat-tab flex w-full items-center gap-2 border-b px-4 py-3 text-left text-sm font-bold hover:bg-white">
        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-400 text-xs font-black text-[#0d3b66]">#</span> Lobby (everyone)
      </button>
      @foreach ($players as $p)
        <button data-with="{{ $p->id }}" data-name="{{ $p->name }}" class="chat-tab flex w-full items-center gap-2 border-b px-4 py-3 text-left text-sm font-semibold hover:bg-white">
          <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1e3a8a] text-xs font-bold text-white">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p->name, 0, 1)) }}</span>
          <span class="truncate">{{ $p->name }}</span>
        </button>
      @endforeach
    </aside>

    <section class="flex min-h-0 flex-col md:col-span-3">
      <header id="chat-title" class="border-b px-4 py-3 text-sm font-extrabold text-[#0d3b66]">Lobby</header>
      <div id="chat-log" class="flex-1 space-y-2 overflow-y-auto bg-slate-50 p-4"></div>
      <form id="chat-form" class="flex gap-2 border-t p-3">
        <input id="chat-input" type="text" maxlength="500" autocomplete="off" placeholder="Type a message..." class="flex-1 rounded-full border border-slate-300 px-4 py-2 text-sm focus:border-[#1e3a8a] focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]/30">
        <button class="rounded-full bg-[#1e3a8a] px-5 text-sm font-bold text-white hover:bg-[#0d3b66]">Send</button>
      </form>
    </section>
  </div>

  <p class="mt-2 text-xs text-slate-500">Be respectful. Messages refresh every few seconds.</p>
@endsection

@push('scripts')
<script>
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const log = document.getElementById('chat-log');
  const input = document.getElementById('chat-input');
  let current = 'lobby', lastId = 0, timer = null;

  function addMessage(m) {
    const row = document.createElement('div');
    row.className = 'flex ' + (m.mine ? 'justify-end' : 'justify-start');
    const bubble = document.createElement('div');
    bubble.className = 'max-w-[80%] rounded-2xl px-3 py-2 text-sm shadow-sm ' + (m.mine ? 'bg-[#1e3a8a] text-white' : 'bg-white text-slate-800');
    if (!m.mine && current === 'lobby') {
      const who = document.createElement('p');
      who.className = 'text-xs font-bold text-[#1e3a8a]';
      who.textContent = m.name;          // textContent: user text is never parsed as HTML
      bubble.appendChild(who);
    }
    const body = document.createElement('p');
    body.className = 'whitespace-pre-wrap break-words';
    body.textContent = m.body;
    const time = document.createElement('p');
    time.className = 'mt-0.5 text-[10px] opacity-60';
    time.textContent = m.time;
    bubble.append(body, time);
    row.appendChild(bubble);
    log.appendChild(row);
  }

  async function poll(reset = false) {
    const target = current;
    try {
      const res = await fetch(`{{ route('chat.messages') }}?with=${encodeURIComponent(target)}&after=${reset ? 0 : lastId}`, { headers: { 'Accept': 'application/json' } });
      if (!res.ok || target !== current) return;
      const msgs = await res.json();
      if (reset) log.innerHTML = '';
      const stick = log.scrollTop + log.clientHeight >= log.scrollHeight - 60;
      msgs.forEach(m => { addMessage(m); lastId = Math.max(lastId, m.id); });
      if (reset || (msgs.length && stick)) log.scrollTop = log.scrollHeight;
    } catch (e) { /* network hiccup: next poll retries */ }
  }

  function openChat(withId, title) {
    current = String(withId); lastId = 0;
    document.getElementById('chat-title').textContent = title;
    document.querySelectorAll('.chat-tab').forEach(b => b.classList.toggle('bg-white', b.dataset.with === current));
    clearInterval(timer);
    poll(true);
    timer = setInterval(() => poll(false), 3000);
  }

  document.querySelectorAll('.chat-tab').forEach(b => b.addEventListener('click', () =>
    openChat(b.dataset.with, b.dataset.with === 'lobby' ? 'Lobby' : b.dataset.name)));

  document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = input.value.trim();
    if (!body) return;
    input.value = '';
    const res = await fetch(`{{ route('chat.send') }}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ with: current, body })
    });
    if (res.ok) poll(false); else input.value = body;
  });

  openChat('lobby', 'Lobby');
</script>
@endpush
