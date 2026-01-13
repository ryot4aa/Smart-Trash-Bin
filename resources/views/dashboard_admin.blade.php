@extends('layouts.app')

@section('content')
<div class="bg-[#fafbfc] min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between min-h-screen py-8 px-4 shadow-sm">
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
                <a href="#" data-menu="user" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-[#f6c90e] bg-[#fff7e6] font-semibold transition duration-200 transform hover:scale-105 hover:translate-x-2">Data User</a>
                <a href="#" data-menu="tukang" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:text-[#f6c90e] hover:bg-[#fff7e6] transition duration-200 transform hover:scale-105 hover:translate-x-2">Data Tukang</a>
                <a href="#" data-menu="pendaftaran_user" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:text-[#f6c90e] hover:bg-[#fff7e6] transition duration-200 transform hover:scale-105 hover:translate-x-2">Pendaftaran User</a>
                <a href="#" data-menu="pendaftaran_tukang" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:text-[#f6c90e] hover:bg-[#fff7e6] transition duration-200 transform hover:scale-105 hover:translate-x-2">Pendaftaran Tukang</a>
                <a href="#" data-menu="acc_tong" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:text-[#f6c90e] hover:bg-[#fff7e6] transition duration-200 transform hover:scale-105 hover:translate-x-2">ACC Tong</a>
            </nav>
        </div>
        <div class="flex-1"></div>
        <div class="text-xs text-black text-center mt-10">&copy; 2025 SmartBin</div>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-h-screen">
        <!-- Header -->
        <header class="flex items-center justify-end bg-white px-10 py-5 shadow-sm border-b border-gray-200">
            <div class="flex items-center gap-4 relative">
                <div class="text-right">
                    <div class="font-bold text-gray-800 text-base">{{ auth()->user()->name }}</div>
                    <div class="text-yellow-400 text-xs font-semibold">Admin</div>
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
            @include('partials.notifikasi', ['notifikasis' => $notifikasis])
            <!-- Grafik untuk Admin -->
            <div class="bg-white rounded-2xl shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-black mb-4">Grafik Ketinggian Sampah</h3>
                <canvas id="adminSensorChart" height="200" class="w-full"></canvas>
                <div class="mt-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Status Buzzer</p>
                        <div id="adminBuzzerStatus" class="px-6 py-3 rounded-lg text-white font-bold text-lg shadow bg-gray-400">Unknown</div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Device terpilih</p>
                        <div id="adminSelectedDeviceName" class="font-semibold text-gray-800">-</div>
                    </div>
                </div>
            </div>
            <div id="menu-user" class="menu-content">
                @include('partials.admin_user_table')
            </div>
            <div id="menu-tukang" class="menu-content hidden">
                @include('partials.admin_tukang_table')
            </div>
            <div id="menu-pendaftaran_user" class="menu-content hidden">
                @include('partials.admin_pendaftaran_user')
            </div>
            <div id="menu-pendaftaran_tukang" class="menu-content hidden">
                @include('partials.admin_pendaftaran_tukang')
            </div>
            <div id="menu-acc_tong" class="menu-content hidden">
                @include('partials.admin_acc_tong')
            </div>
        </section>
    </main>
</div>
<script>
    // Sidebar menu switching
    document.querySelectorAll('.sidebar-link').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.sidebar-link').forEach(function(link) {
                link.classList.remove('text-[#f6c90e]', 'bg-[#fff7e6]', 'font-semibold');
                link.classList.add('text-gray-700');
            });
            btn.classList.add('text-[#f6c90e]', 'bg-[#fff7e6]', 'font-semibold');
            btn.classList.remove('text-gray-700');
            document.querySelectorAll('.menu-content').forEach(function(content) {
                content.classList.add('hidden');
            });
            document.getElementById('menu-' + btn.dataset.menu).classList.remove('hidden');
        });
    });
    // Profile dropdown
    document.getElementById('profileDropdownBtn').addEventListener('click', function() {
        document.getElementById('profileDropdown').classList.toggle('hidden');
    });
    document.addEventListener('click', function(e) {
        if (!document.getElementById('profileDropdownBtn').contains(e.target) && !document.getElementById('profileDropdown').contains(e.target)) {
            document.getElementById('profileDropdown').classList.add('hidden');
        }
    });
    // Chart initialization for admin
    let adminSensorChart;
    let adminSelectedDeviceId = null;
    let adminDevicesCache = [];
    
    (function initAdminChart() {
        const canvasEl = document.getElementById('adminSensorChart');
        if (!canvasEl || !canvasEl.getContext) {
            console.warn('adminSensorChart canvas not found');
            adminSensorChart = { data: { labels: [], datasets: [{ label: 'Ketinggian Sampah (cm)', data: [] }] }, update: function() {} };
            return;
        }
        const ctx = canvasEl.getContext('2d');
        adminSensorChart = new Chart(ctx, {
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
    
    async function fetchAdminSensorData(deviceId = null) {
        let url = '/api/sensor-readings';
        if (deviceId) url += '?device_id=' + deviceId;
        const response = await fetch(url, { credentials: 'same-origin' });
        return response.json();
    }
    
    async function fetchAllDevices() {
        const response = await fetch('/api/devices', { credentials: 'same-origin' });
        const data = await response.json();
        adminDevicesCache = Array.isArray(data) ? data : (data?.data ?? []);
        return adminDevicesCache;
    }
    
    async function updateAdminChart(deviceId = null) {
        const sensorData = await fetchAdminSensorData(deviceId);
        const devices = await fetchAllDevices();
        adminSensorChart.data.labels = sensorData.map(d => new Date(d.timestamp).toLocaleTimeString());
        adminSensorChart.data.datasets[0].data = sensorData.map(d => d.value);
        let label = 'Ketinggian Sampah (cm)';
        if (deviceId && adminDevicesCache.length > 0) {
            const device = adminDevicesCache.find(d => d.id == deviceId);
            if (device && device.nama_device) label = device.nama_device + ' (cm)';
        }
        adminSensorChart.data.datasets[0].label = label;
        adminSensorChart.update();
        let buzzerStatus = adminDevicesCache[0]?.buzzer_status;
        const buzzerEl = document.getElementById('adminBuzzerStatus');
        if (buzzerEl) {
            buzzerEl.textContent = buzzerStatus || 'Unknown';
            buzzerEl.className = `px-6 py-3 rounded-lg text-white font-bold text-lg shadow ${String(buzzerStatus).toLowerCase() === 'on' ? 'bg-green-500' : String(buzzerStatus).toLowerCase() === 'off' ? 'bg-red-500' : 'bg-gray-400'}`;
        }
        const nameEl = document.getElementById('adminSelectedDeviceName');
        if (nameEl && deviceId && adminDevicesCache.length > 0) {
            const device = adminDevicesCache.find(d => d.id == deviceId);
            nameEl.textContent = device?.nama_device ?? '-';
        }
    }
    
    fetchAllDevices().then(devices => {
        if (devices.length > 0) {
            adminSelectedDeviceId = devices[0].id;
            updateAdminChart(adminSelectedDeviceId);
        }
    });
</script>
@endsection
