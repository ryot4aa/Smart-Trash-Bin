@php
    $user = auth()->user();
    $profilePhoto = $user->profile_photo ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-[#fafbfc] min-h-screen flex">
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between min-h-screen py-8 px-4 shadow-sm">
        <div>
            <div class="flex items-center gap-3 mb-10 px-2">
                <img src="https://cdn-icons-png.flaticon.com/512/3097/3097140.png" class="h-8 w-8" alt="Logo">
                <span class="text-xl font-bold text-[#f6c90e] tracking-wide">SmartBin</span>
            </div>
            <nav class="flex flex-col gap-1">
                @php
                    $dashboardUrl = $user->role === 'admin' ? '/dashboard/admin' : ($user->role === 'tukang' ? '/dashboard/tukang' : '/dashboard/user');
                @endphp
                <a href="{{ $dashboardUrl }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:text-[#f6c90e] hover:bg-[#fff7e6] transition duration-200 transform hover:scale-105 hover:translate-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L15 12l-5.25-5"/></svg>
                    Dashboard
                </a>
                <a href="/profile" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#f6c90e] bg-[#fff7e6] font-semibold transition duration-200 transform hover:scale-105 hover:translate-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Profile
                </a>
            </nav>
        </div>
        <div class="text-xs text-[#f6c90e] text-center mt-10">&copy; 2025 SmartBin</div>
    </aside>
    <main class="flex-1 flex flex-col min-h-screen">
        <header class="flex items-center justify-end bg-white px-10 py-5 shadow-sm border-b border-gray-200">
            <div class="flex items-center gap-4 relative">
                <div class="text-right">
                    <div class="font-bold text-gray-800 text-base">{{ $user->name }}</div>
                    <div class="text-[#f6c90e] text-xs font-semibold">{{ ucfirst($user->role) }}</div>
                </div>
                <button id="profileDropdownBtn" class="focus:outline-none">
                    <img src="{{ $profilePhoto ? asset('storage/' . $profilePhoto) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=f6c90e&color=222b45&size=128' }}" alt="Foto Profil" class="h-10 w-10 rounded-full border-2 border-[#f6c90e] shadow object-cover">
                </button>
                <div id="profileDropdown" class="hidden absolute right-0 top-14 bg-white rounded-lg shadow-lg border border-gray-100 w-40 z-50">
                    <a href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
                    </form>
                </div>
            </div>
        </header>
        <section class="flex-1 flex justify-center items-start p-8 bg-[#fafbfc] min-h-[calc(100vh-80px)]">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow p-8">
                <h2 class="text-2xl font-bold text-[#222b45] mb-6">Profile Information</h2>
                <div class="flex flex-col items-center mb-6">
                    <img src="{{ $profilePhoto ? asset('storage/' . $profilePhoto) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=f6c90e&color=222b45&size=256' }}" alt="Foto Profil" class="h-32 w-32 rounded-full border-4 border-[#f6c90e] shadow object-cover mb-4">
                    @if($user->role !== 'tukang')
                    <form action="{{ route('profile.uploadPhoto') }}" method="POST" enctype="multipart/form-data" class="flex flex-col items-center w-full mb-2">
                        @csrf
                        <input type="file" name="profile_photo" accept="image/*" class="mb-2 px-2 py-1 border border-gray-300 rounded w-full">
                        <button type="submit" class="bg-[#f6c90e] hover:bg-yellow-400 text-[#222b45] font-bold py-1 px-4 rounded shadow transition">Upload Foto</button>
                    </form>
                    @endif
                    <div class="text-lg font-bold text-gray-800">{{ $user->name }}</div>
                    <div class="text-[#f6c90e] text-sm font-semibold">{{ ucfirst($user->role) }}</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-500 text-xs mb-1">Email</label>
                        <input type="text" value="{{ $user->email }}" class="w-full px-4 py-2 border border-gray-200 rounded bg-gray-50 text-gray-700" readonly>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs mb-1">Nomor Telepon</label>
                        <input type="text" value="{{ $user->phone ?? '-' }}" class="w-full px-4 py-2 border border-gray-200 rounded bg-gray-50 text-gray-700" readonly>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs mb-1">Alamat</label>
                        <input type="text" value="{{ $user->address ?? '-' }}" class="w-full px-4 py-2 border border-gray-200 rounded bg-gray-50 text-gray-700" readonly>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs mb-1">Tanggal Bergabung</label>
                        <input type="text" value="{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}" class="w-full px-4 py-2 border border-gray-200 rounded bg-gray-50 text-gray-700" readonly>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        // Dropdown profile
        const btn = document.getElementById('profileDropdownBtn');
        const dropdown = document.getElementById('profileDropdown');
        if(btn && dropdown) {
            btn.addEventListener('click', () => {
                dropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
