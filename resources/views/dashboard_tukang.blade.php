@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col h-screen py-8 px-4 shadow-sm">
        <div class="flex flex-col h-screen">
            <div>
                <div class="flex flex-col items-center mb-8">
                    <div class="flex items-center gap-3 mb-4 px-2">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6m2 0a2 2 0 012 2v2H5V5a2 2 0 012-2m3 0V1m0 0h2m-2 0v2m0 0h2m-2 0v2m-7 4h18m-2 0v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7z"/>
                        </svg>
                        <span class="text-xl font-bold text-black tracking-wide">SmartBin</span>
                    </div>
                </div>
                <nav class="flex flex-col gap-1" id="sidebarMenu">
                    <a href="/dashboard" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-[#f6c90e] bg-[#fff7e6] font-semibold transition duration-200 transform hover:scale-105 hover:translate-x-2">Dashboard</a>
                </nav>
            </div>
            <div class="flex-1"></div>
            <div class="text-xs text-black text-center mt-auto">&copy; 2025 SmartBin</div>
        </div>
    </aside>
    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">
        <header class="flex items-center justify-end bg-white px-10 py-5 shadow-sm border-b border-gray-200">
            <div class="flex items-center gap-4 relative">
                <div class="text-right">
                    <div class="font-bold text-gray-800 text-base">{{ auth()->user()->name }}</div>
                    <div class="text-yellow-400 text-xs font-semibold">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
                <button id="profileDropdownBtn" class="focus:outline-none">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=f6c90e&color=222b45&size=128" alt="Avatar" class="h-10 w-10 rounded-full border-2 border-[#f6c90e] shadow">
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
        <!-- Content -->
        <section class="flex-1 p-8 bg-[#fafbfc] min-h-[calc(100vh-80px)]">
                        @if(isset($notifikasis) && count($notifikasis) > 0)
                            <div class="mb-6">
                                @include('partials.notifikasi', ['notifikasis' => $notifikasis])
                            </div>
                        @endif
            <!-- Grafik untuk Tukang -->
            <div class="bg-white rounded-2xl shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-black mb-4">Grafik Ketinggian Sampah</h3>
                <canvas id="tukangSensorChart" height="200" class="w-full"></canvas>
                <div class="mt-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Status Buzzer</p>
                        <div id="tukangBuzzerStatus" class="px-6 py-3 rounded-lg text-white font-bold text-lg shadow bg-gray-400">Unknown</div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Device terpilih</p>
                        <div id="tukangSelectedDeviceName" class="font-semibold text-gray-800">-</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-xl font-semibold mb-2 text-black text-center">Daftar Tempat Sampah</h2>
                <div class="mb-4 w-full">
                    <input type="text" id="search-user-tong" placeholder="Cari Nama User..." class="border rounded px-3 py-2 w-full" />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed bg-white rounded-2xl overflow-hidden shadow-lg">
                        <colgroup>
                            <col style="width: 10%">
                            <col style="width: 30%">
                            <col style="width: 40%">
                            <col style="width: 20%">
                        </colgroup>
                        <thead class="bg-white">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">ID</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">Nama Tong Sampah</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">Nama User</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">Lokasi</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">Status</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">Kadar Gas</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">Presentase Sampah</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-black uppercase tracking-wider">Cleaning Status</th>
                            </tr>
                        </thead>
                        <tbody id="deviceTableBody" class="divide-y divide-blue-100">
                            @forelse($devices as $device)
                            <tr class='hover:bg-[#fff7e6] transition'>
                                <td class='py-2 px-6 device-id'>{{ $device->id }}</td>
                                <td class='py-2 px-6 device-nama'>{{ $device->nama_device ?? '-' }}</td>
                                <td class='py-2 px-6 device-user'>{{ $device->user->name ?? '-' }}</td>
                                <td class='py-2 px-6'>{{ $device->lokasi ?? '-' }}</td>
                                <td class='py-2 px-6'>
                                    @if($device->status === 'pending')
                                        <span class="text-yellow-500 font-semibold">Pending</span>
                                    @elseif($device->status === 'online')
                                        <span class="text-green-600 font-semibold">Online</span>
                                    @else
                                        <span class="text-red-600 font-semibold">Offline</span>
                                    @endif
                                </td>
                                <td class='py-2 px-6'>{{ $device->latestReading->gas ?? '-' }}</td>
                                <td class='py-2 px-6'>{{ isset($device->latestReading->volume) ? $device->latestReading->volume . '%' : '-' }}</td>
                                <td class='py-2 px-6'>
                                    <form method="POST" action="{{ route('tukang.cleaning.update', $device->id) }}">
                                        @csrf
                                        <select name="status" class="border rounded px-2 py-1 text-sm font-semibold {{ isset($device->cleaning_status) && $device->cleaning_status === 'sudah' ? 'text-green-600' : 'text-red-600' }}" onchange="this.form.submit()">
                                            <option value="belum" {{ (!isset($device->cleaning_status) || $device->cleaning_status === 'belum') ? 'selected' : '' }}>Belum</option>
                                            <option value="sudah" {{ (isset($device->cleaning_status) && $device->cleaning_status === 'sudah') ? 'selected' : '' }}>Sudah</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-gray-400">Data belum ada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
            <!-- Data User mirip admin -->
            <div class="bg-white rounded-lg shadow p-6 mt-8">
                <h2 class="text-lg font-bold mb-4">Data User</h2>
                <div class="mb-4">
                    <input type="text" id="search-user" placeholder="Cari user..." class="border rounded px-3 py-2 w-full" />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b text-center">Nama</th>
                                <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b text-center">Email</th>
                                <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Jumlah Tong</th>
                                <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Status</th>
                                <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Kadar Gas</th>
                                <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Presentase Sampah</th>
                                <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Status Clean</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-4 py-2 border-b text-center">{{ $user->name }}</td>
                                    <td class="px-4 py-2 border-b text-center">{{ $user->email }}</td>
                                    <td class="px-4 py-2 border-b text-center">{{ $user->devices->count() }}</td>
                                    <td class="px-4 py-2 border-b text-center">
                                        @php
                                            $online = $user->devices->where('status', 'online');
                                            $offline = $user->devices->where('status', 'offline');
                                            $pending = $user->devices->where('status', 'pending');
                                            $onlineCount = $online->count();
                                            $offlineCount = $offline->count();
                                            $pendingCount = $pending->count();
                                            $onlineNames = $online->pluck('nama_device')->filter()->implode(', ');
                                            $offlineNames = $offline->pluck('nama_device')->filter()->implode(', ');
                                            $pendingNames = $pending->pluck('nama_device')->filter()->implode(', ');
                                        @endphp
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-green-600 font-semibold" title="@if($onlineCount>0)Online: {{ $onlineNames }}@endif">Online: {{ $onlineCount }}</span>
                                            @if($offlineCount > 0)
                                                <span class="text-red-600 font-semibold" title="@if($offlineCount>0)Offline: {{ $offlineNames }}@endif">Offline: {{ $offlineCount }}</span>
                                            @endif
                                            @if($pendingCount > 0)
                                                <span class="text-yellow-500 font-semibold" title="@if($pendingCount>0)Pending: {{ $pendingNames }}@endif">Pending: {{ $pendingCount }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border-b text-center">
                                        @if($user->devices->count() > 0)
                                            <ul>
                                            @foreach($user->devices as $device)
                                                <li>
                                                    <span class="font-semibold">{{ $device->nama_device ?? 'Device' }}</span>:
                                                    @if($device->latestReading)
                                                        {{ $device->latestReading->gas ?? '-' }}
                                                    @else
                                                        -
                                                    @endif
                                                </li>
                                            @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b text-center">
                                        @if($user->devices->count() > 0)
                                            <ul>
                                            @foreach($user->devices as $device)
                                                <li>
                                                    <span class="font-semibold">{{ $device->nama_device ?? 'Device' }}</span>:
                                                    @if($device->latestReading)
                                                        {{ $device->latestReading->volume ?? '-' }}%
                                                    @else
                                                        -
                                                    @endif
                                                </li>
                                            @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b text-center">
                                        @if($user->devices->count() > 0)
                                            <ul>
                                            @foreach($user->devices as $device)
                                                <li>
                                                    <span class="font-semibold">{{ $device->nama_device ?? 'Device' }}</span>:
                                                    @if(isset($device->cleaning_status))
                                                        @if($device->cleaning_status === 'sudah')
                                                            <span class="text-green-600 font-semibold">Sudah dibersihkan</span>
                                                        @else
                                                            <span class="text-red-600 font-semibold">Belum dibersihkan</span>
                                                        @endif
                                                    @else
                                                        -
                                                    @endif
                                                </li>
                                            @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-gray-400">Tidak ada data user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('profileDropdownBtn');
    const dropdown = document.getElementById('profileDropdown');
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', function(e) {
        if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart initialization for tukang
let tukangSensorChart;
let tukangSelectedDeviceId = null;
let tukangDevicesCache = [];

(function initTukangChart() {
    const canvasEl = document.getElementById('tukangSensorChart');
    if (!canvasEl || !canvasEl.getContext) {
        console.warn('tukangSensorChart canvas not found');
        tukangSensorChart = { data: { labels: [], datasets: [{ label: 'Ketinggian Sampah (cm)', data: [] }] }, update: function() {} };
        return;
    }
    const ctx = canvasEl.getContext('2d');
    tukangSensorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Ketinggian Sampah (cm)',
                data: [],
                backgroundColor: 'rgba(246,201,14,0.2)',
                borderColor: 'rgba(246,201,14,1)',
                borderWidth: 2,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(246,201,14,1)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#f6c90e', font: { weight: 'bold' } } } },
            scales: { y: { beginAtZero: true, ticks: { color: '#f6c90e' } }, x: { ticks: { color: '#f6c90e' } } }
        }
    });
})();

async function fetchTukangSensorData(deviceId = null) {
    let url = '/api/sensor-readings';
    if (deviceId) url += '?device_id=' + deviceId;
    const response = await fetch(url, { credentials: 'same-origin' });
    return response.json();
}

async function fetchTukangAllDevices() {
    const response = await fetch('/api/devices', { credentials: 'same-origin' });
    const data = await response.json();
    tukangDevicesCache = Array.isArray(data) ? data : (data?.data ?? []);
    return tukangDevicesCache;
}

async function updateTukangChart(deviceId = null) {
    const sensorData = await fetchTukangSensorData(deviceId);
    const devices = await fetchTukangAllDevices();
    tukangSensorChart.data.labels = sensorData.map(d => new Date(d.timestamp).toLocaleTimeString());
    tukangSensorChart.data.datasets[0].data = sensorData.map(d => d.value);
    let label = 'Ketinggian Sampah (cm)';
    if (deviceId && tukangDevicesCache.length > 0) {
        const device = tukangDevicesCache.find(d => d.id == deviceId);
        if (device && device.nama_device) label = device.nama_device + ' (cm)';
    }
    tukangSensorChart.data.datasets[0].label = label;
    tukangSensorChart.update();
    let buzzerStatus = tukangDevicesCache[0]?.buzzer_status;
    const buzzerEl = document.getElementById('tukangBuzzerStatus');
    if (buzzerEl) {
        buzzerEl.textContent = buzzerStatus || 'Unknown';
        buzzerEl.className = `px-6 py-3 rounded-lg text-white font-bold text-lg shadow ${String(buzzerStatus).toLowerCase() === 'on' ? 'bg-green-500' : String(buzzerStatus).toLowerCase() === 'off' ? 'bg-red-500' : 'bg-gray-400'}`;
    }
    const nameEl = document.getElementById('tukangSelectedDeviceName');
    if (nameEl && deviceId && tukangDevicesCache.length > 0) {
        const device = tukangDevicesCache.find(d => d.id == deviceId);
        nameEl.textContent = device?.nama_device ?? '-';
    }
}

fetchTukangAllDevices().then(devices => {
    if (devices.length > 0) {
        tukangSelectedDeviceId = devices[0].id;
        updateTukangChart(tukangSelectedDeviceId);
    }
});

// Add click handlers to device rows
setTimeout(() => {
    const rows = document.querySelectorAll('#deviceTableBody tr');
    rows.forEach(row => {
        row.addEventListener('click', async () => {
            const deviceId = row.querySelector('.device-id')?.textContent;
            if (deviceId) {
                tukangSelectedDeviceId = parseInt(deviceId);
                await updateTukangChart(tukangSelectedDeviceId);
            }
        });
    });
}, 500);
</script>
@endsection
@section('scripts')
<script>
document.getElementById('search-user')?.addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#user-table-body tr').forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>
@section('scripts')
<script>
document.getElementById('search-user-tong')?.addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#deviceTableBody tr').forEach(function(row) {
        const userCell = row.querySelector('.device-user');
        if (!userCell) return;
        const userName = userCell.textContent.toLowerCase();
        row.style.display = userName.includes(search) ? '' : 'none';
    });
});
</script>
@endsection
@endsection
