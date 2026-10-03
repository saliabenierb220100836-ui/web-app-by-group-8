@if (session('success'))
  <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
@endif

@if (session('new_password'))
  <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
    <p class="font-bold">Give these login details to the member now. The password is shown only once.</p>
    <p class="mt-1 font-mono">Email: {{ session('new_password')['email'] }} &nbsp;|&nbsp; Password: {{ session('new_password')['password'] }}</p>
  </div>
@endif

@if ($errors->any())
  <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <ul class="list-disc space-y-1 pl-5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
