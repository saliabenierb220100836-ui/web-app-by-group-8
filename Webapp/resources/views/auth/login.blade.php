<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Coinnect</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .cn-blue-dark { background-color: #0d3b66; }
    .cn-blue-main { background-color: #1e3a8a; }
    .cn-text-blue { color: #1e3a8a; }
    .gold-ring {
      background: conic-gradient(
        #ffd700 0deg 120deg,
        #ffcc00 120deg 240deg,
        #b8860b 240deg 360deg
      );
    }

    body {
      background: linear-gradient(135deg, #cfd3db 0%, #a9afba 50%, #7c818a 100%);
      min-height: 100vh;
      font-family: sans-serif;
      position: relative;
      overflow: hidden;
    }
    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background-image: 
        linear-gradient(#00000012 1px, transparent 1px),
        linear-gradient(90deg, #00000012 1px, transparent 1px);
      background-size: 40px 40px;
      pointer-events: none;
    }
    body::after {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at center, transparent 65%, rgba(0,0,0,0.25) 100%);
      pointer-events: none;
    }
  </style>
</head>
<body class="bg-[#f0f4fa] min-h-screen flex items-center justify-center p-4 font-sans">

  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 relative z-10">
    <div class="cn-blue-dark p-8 text-center flex flex-col items-center justify-center">
      <div class="relative w-24 h-24 rounded-full p-1 gold-ring shadow-lg mb-3">
        <div class="w-full h-full bg-[#ffd700] rounded-full flex items-center justify-center overflow-hidden">
          <svg viewBox="0 0 100 100" class="w-16 h-16 fill-white">
            <circle cx="50" cy="50" r="20" />
            <path d="M40 50 L60 50 M50 40 L50 60" stroke="white" stroke-width="4"/>
          </svg>
        </div>
      </div>

      <h1 class="text-2xl font-extrabold text-white tracking-wide">COINNECT</h1>
      <p class="text-blue-200 text-sm mt-1 font-medium">Welcome back to the Hub 🎮</p>
    </div>

    <!-- Form Body -->
    <form action="{{ route('login') }}" method="POST" class="p-8 space-y-4">
      @csrf

      <div>
        <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required
               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#1e3a8a] focus:border-transparent transition"
               placeholder="name@example.com">
        @error('email')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <div class="flex justify-between items-center mb-1">
          <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Password</label>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-xs cn-text-blue font-semibold hover:underline">
              Forgot?
            </a>
          @endif
        </div>
        <input type="password" id="password" name="password" required
               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#1e3a8a] focus:border-transparent transition"
               placeholder="••••••••">
        @error('password')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex items-center">
        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-[#1e3a8a] focus:ring-[#1e3a8a] border-gray-300 rounded">
        <label for="remember" class="ml-2 text-xs text-gray-600 font-medium">Remember me</label>
      </div>

      <button type="submit" 
              class="w-full mt-2 cn-blue-main hover:bg-[#0d3b66] text-white font-bold py-3.5 px-4 rounded-full shadow-md hover:shadow-lg transition duration-200">
        Sign In
      </button>
    </form>

    <!-- Footer link -->
    <div class="px-8 pb-8 text-center bg-gray-50 border-t border-gray-100 pt-4">
      <p class="text-sm text-gray-600">New to Coinnect?</p>
      <a href="{{ route('register') }}" class="cn-text-blue font-bold text-sm hover:underline mt-1 inline-block">
        Create a Coinnect Account
      </a>
    </div>
  </div>

</body>
</html>