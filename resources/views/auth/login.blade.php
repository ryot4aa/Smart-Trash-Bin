<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="min-h-screen flex bg-[#151c2c]">
    <div class="flex flex-1 min-h-screen">
        <!-- Left: Illustration & Branding -->
        <div class="hidden md:flex flex-col justify-center items-center flex-1 bg-[#1a2238] p-10">
            <svg class="h-20 w-20 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6m2 0a2 2 0 012 2v2H5V5a2 2 0 012-2m3 0V1m0 0h2m-2 0v2m0 0h2m-2 0v2m-7 4h18m-2 0v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7z"/>
            </svg>
            <h1 class="text-3xl font-bold text-[#f6c90e] mb-4 tracking-wide">SmartBin Monitoring</h1>
            <p class="text-[#f6c90e] text-lg font-semibold text-center">Pantau status dan data tempat sampah otomatis Anda secara real-time.</p>
        </div>
        <!-- Right: Login Form -->
        <div class="flex flex-col justify-center items-center flex-1 bg-white rounded-l-3xl shadow-2xl p-10 animate-fade-in">
            <div class="w-full max-w-md">
                <h2 class="text-3xl font-extrabold text-[#222b45] mb-2 tracking-tight">Login</h2>
                <p class="text-gray-500 text-base mb-6">Masuk ke akun Anda untuk monitoring</p>
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-400 text-red-700 px-4 py-2 rounded-lg text-center animate-pulse shadow">
                        <svg class="inline w-5 h-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ session('error') }}
                    </div>
                @endif
                <form method="POST" action="/login" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[#222b45] mb-1 font-semibold" for="email">Email</label>
                        <input class="w-full px-4 py-2 border-2 border-[#f6c90e] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#f6c90e] transition" type="email" name="email" id="email" required autofocus placeholder="you@email.com">
                    </div>
                    <div>
                        <label class="block text-[#222b45] mb-1 font-semibold" for="password">Password</label>
                        <input class="w-full px-4 py-2 border-2 border-[#f6c90e] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#f6c90e] transition" type="password" name="password" id="password" required placeholder="********">
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-[#f6c90e] to-yellow-400 hover:from-yellow-400 hover:to-[#f6c90e] text-[#222b45] font-bold py-2 px-4 rounded-xl shadow-lg transition">Login</button>
                </form>
            </div>
        </div>
    </div>
    <style>
        .animate-fade-in {
            animation: fadeIn 0.7s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
