<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coinnect - Insert Coin, Connect Fun</title>
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

    nav {
      background: linear-gradient(to right, #ffffff, #f7f9fc);
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
      border-bottom: 3px solid #ffd700; /* gold accent line */
    }
    
  </style>
</head>
<body class="flex flex-col font-sans">

  <!-- Top Navigation Bar -->
  <nav class="px-6 py-4 flex justify-between items-center relative z-10">
    <div class="flex items-center space-x-3">
      <div class="w-10 h-10 rounded-full p-0.5 gold-ring">
        <div class="w-full h-full bg-[#ffd700] rounded-full flex items-center justify-center overflow-hidden">
          <svg viewBox="0 0 100 100" class="w-7 h-7 fill-white">
            <circle cx="50" cy="50" r="20" />
            <path d="M40 50 L60 50 M50 40 L50 60" stroke="white" stroke-width="4"/>
          </svg>
        </div>
      </div>
      <span class="text-xl font-extrabold tracking-wider cn-text-blue">COINNECT</span>
    </div>

    <div class="flex items-center space-x-3">
      @auth
        <a href="{{ url('/dashboard') }}" class="cn-blue-main hover:bg-[#0d3b66] text-white font-bold py-2 px-5 rounded-full text-sm transition">
          Dashboard
        </a>
      @else
        <a href="{{ route('login') }}" class="text-gray-700 hover:text-[#1e3a8a] font-semibold text-sm px-3 py-2 transition">
          Log in
        </a>
        <a href="{{ route('register') }}" class="cn-blue-main hover:bg-[#0d3b66] text-white font-bold py-2 px-5 rounded-full text-sm transition shadow-sm">
          Register
        </a>
      @endauth
    </div>
  </nav>

  <!-- Main Hero Section -->
  <main class="flex-grow flex items-center justify-center p-6 relative z-10">
    <div class="max-w-xl text-center space-y-6">
      <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 leading-tight">
        Welcome to the Hub. <br>
        <span class="cn-text-blue">Insert Coin, Connect Fun 🎮</span>
      </h1>
      <p class="text-gray-700 text-base sm:text-lg">
        Welcome to Coinnect — your ultimate gaming hub. Jump into epic LAN battles, chill with friends, and level up your experience in a space built for gamers.
      </p>
      <div class="flex justify-center gap-4 pt-2">
        <a href="{{ route('register') }}" class="cn-blue-main hover:bg-[#0d3b66] text-white font-bold py-3.5 px-8 rounded-full shadow-lg transition">
          Join the Game
        </a>
        <a href="{{ route('login') }}" class="bg-white hover:bg-gray-50 text-gray-800 font-bold py-3.5 px-8 rounded-full border border-gray-300 shadow-sm transition">
          Sign In
        </a>
      </div>
    </div>
  </main>

</body>
</html>