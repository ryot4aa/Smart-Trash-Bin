<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-[#fafbfc] min-h-screen flex">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between min-h-screen py-8 px-4 shadow-sm">
        <div>
            <div class="flex flex-col items-center mb-8">
                <div class="flex items-center gap-3 mb-4 px-2">
                    <!-- Ganti logo mobil dengan SVG ikon tong sampah -->
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6m2 0a2 2 0 012 2v2H5V5a2 2 0 012-2m3 0V1m0 0h2m-2 0v2m0 0h2m-2 0v2m-7 4h18m-2 0v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7z"/>
                    </svg>
                    <span class="text-xl font-bold text-[#f6c90e] tracking-wide">SmartBin</span>
                </div>
                <!-- Tombol dark mode di-nonaktifkan -->
            </div>
            <nav class="flex flex-col gap-1" id="sidebarMenu">
                <a href="#" data-menu="dashboard" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-semibold transition duration-200 transform hover:scale-105 hover:translate-x-2 hover:text-yellow-400 hover:bg-yellow-100 active:bg-yellow-100 active:text-yellow-400">Dashboard</a>
                <a href="#" data-menu="pendaftaran" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-semibold transition duration-200 transform hover:scale-105 hover:translate-x-2 hover:text-yellow-400 hover:bg-yellow-100 active:bg-yellow-100 active:text-yellow-400">Pendaftaran Tong</a>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebarLinks = document.querySelectorAll('#sidebarMenu .sidebar-link');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        sidebarLinks.forEach(l => l.classList.remove('bg-yellow-100', 'text-yellow-400'));
                        this.classList.add('bg-yellow-100', 'text-yellow-400');
                    });
                });
            });
            </script>
            </nav>
        </div>
        <div class="text-xs text-[#f6c90e] text-center mt-10">&copy; 2025 SmartBin</div>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-h-screen">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
        <!-- Header -->
        <header class="flex items-center justify-end bg-white px-10 py-5 shadow-sm border-b border-gray-200">
            <div class="flex items-center gap-4 relative">
                <div class="text-right">
                    <div class="font-bold text-gray-800 text-base">{{ auth()->user()->name }}</div>
                    <div class="text-yellow-400 text-xs font-semibold">User</div>
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
                        <div id="notifikasi-container" class="mb-6">
                            @if(isset($notifikasis) && count($notifikasis) > 0)
                                @include('partials.notifikasi', ['notifikasis' => $notifikasis])
                            @endif
                        </div>
            <div id="content-dashboard" class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <!-- Grafik dan ringkasan device -->
                <div class="bg-white rounded-2xl shadow p-6">
                    <h3 class="text-lg font-semibold text-[#f6c90e] mb-4">Grafik Ketinggian Sampah</h3>
                    <canvas id="sensorChart" height="200" class="w-full"></canvas>
                    <div class="mt-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Status LED</p>
                            <div id="ledStatus" class="px-6 py-3 rounded-lg text-white font-bold text-lg shadow bg-gray-400">Unknown</div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Device terpilih</p>
                            <div id="selectedDeviceName" class="font-semibold text-gray-800">-</div>
                        </div>
                    </div>
                </div>
                <!-- Placeholder untuk konten lain (opsional) -->
                <div class="bg-white rounded-2xl shadow p-6">
                    <h3 class="text-lg font-semibold text-[#f6c90e] mb-4">Ringkasan</h3>
                    <p class="text-sm text-gray-600">Daftar tong sampah akan ditampilkan pada tabel di bawah.</p>
                </div>
            </div>
            <div id="content-pendaftaran" class="bg-white rounded-2xl shadow p-6 mb-10 hidden">
                <h2 class="text-xl font-semibold mb-4 text-[#f6c90e]">Daftarkan Tong Sampah Baru</h2>
                <form action="/devices/register" method="POST" class="flex flex-col md:flex-row gap-4 items-center">
                    @csrf
                    <input type="text" name="nama_device" placeholder="Nama Tong Sampah" required class="flex-1 px-4 py-2 border-2 border-[#f6c90e] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#f6c90e] bg-white text-gray-800 transition">
                    <input type="text" name="lokasi" placeholder="Lokasi Tong Sampah" required class="flex-1 px-4 py-2 border-2 border-[#f6c90e] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#f6c90e] bg-white text-gray-800 transition">
                    <button type="submit" class="bg-gradient-to-r from-[#f6c90e] to-yellow-400 hover:from-yellow-400 hover:to-[#f6c90e] text-[#222b45] font-bold py-2 px-8 rounded-xl shadow-lg transition">Daftarkan</button>
                </form>
                @if(session('success'))
                    <div class="mt-4 bg-green-50 border border-green-400 text-green-700 px-4 py-2 rounded-lg text-center animate-pulse shadow">
                        <svg class="inline w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
            </div>
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-[#f6c90e] text-center">Daftar Tong Sampah Anda</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed bg-white rounded-2xl overflow-hidden shadow-lg">
                        <colgroup>
                            <col style="width: 10%">
                            <col style="width: 30%">
                            <col style="width: 40%">
                            <col style="width: 20%">
                        </colgroup>
                        <thead class="bg-[#f6c90e]">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-bold text-[#222b45] uppercase tracking-wider">ID</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-[#222b45] uppercase tracking-wider">Nama</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-[#222b45] uppercase tracking-wider">Lokasi</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-[#222b45] uppercase tracking-wider">STATUS</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-[#222b45] uppercase tracking-wider">Kadar Gas</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-[#222b45] uppercase tracking-wider">Presentase Sampah</th>
                                <th class="py-3 px-4 text-left text-xs font-bold text-[#222b45] uppercase tracking-wider">Cleaning Status</th>
                            </tr>
                        </thead>
                        <tbody id="userDeviceTableBody" class="divide-y divide-blue-100">
                            <!-- Data akan di-load via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
                    // Dark mode logic removed
        </script>
    <script>
        // Dropdown profile
        const btn = document.getElementById('profileDropdownBtn');
        const dropdown = document.getElementById('profileDropdown');
        btn.addEventListener('click', () => {
            dropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function(e) {
            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
        // Sidebar menu switching
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        const contentDashboard = document.getElementById('content-dashboard');
        const contentPendaftaran = document.getElementById('content-pendaftaran');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                sidebarLinks.forEach(l => l.classList.remove('text-[#f6c90e]', 'bg-[#fff7e6]', 'font-semibold'));
                this.classList.add('text-[#f6c90e]', 'bg-[#fff7e6]', 'font-semibold');
                if(this.dataset.menu === 'pendaftaran') {
                    contentDashboard.classList.add('hidden');
                    contentPendaftaran.classList.remove('hidden');
                } else {
                    contentDashboard.classList.remove('hidden');
                    contentPendaftaran.classList.add('hidden');
                }
            });
        });
        // Chart & status
        // Grafik per tong
        let selectedDeviceId = null;
        async function fetchSensorData(deviceId = null) {
            let url = '/api/sensor-readings';
            if (deviceId) url += '?device_id=' + deviceId;
            const response = await fetch(url, { credentials: 'same-origin' });
            return response.json();
        }
        let userDevicesCache = [];
        async function fetchUserDevices() {
            const response = await fetch('/api/my-devices', { credentials: 'same-origin' });
            const data = await response.json();
            userDevicesCache = data;
            return data;
        }
        async function fetchLedStatus(deviceId = null) {
            let url = '/api/devices';
            if (deviceId) url += '?device_id=' + deviceId;
            const response = await fetch(url, { credentials: 'same-origin' });
            return response.json();
        }
        let sensorChart;
        (function initChart() {
            const canvasEl = document.getElementById('sensorChart');
            if (!canvasEl || !canvasEl.getContext) {
                console.warn('sensorChart canvas not found — chart disabled');
                sensorChart = {
                    data: { labels: [], datasets: [{ label: 'Ketinggian Sampah (cm)', data: [] }] },
                    update: function() {}
                };
                return;
            }
            const ctx = canvasEl.getContext('2d');
            sensorChart = new Chart(ctx, {
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
                    plugins: {
                        legend: {
                            labels: { color: '#f6c90e', font: { weight: 'bold' } }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#f6c90e' }
                        },
                        x: {
                            ticks: { color: '#f6c90e' }
                        }
                    }
                }
            });
        })();
        async function updateChartAndStatus(deviceId = null) {
            const sensorData = await fetchSensorData(deviceId);
            const ledData = await fetchLedStatus(deviceId);
            // Normalize response shape: API may return { data: [...] } or an array
            const devicesList = Array.isArray(ledData) ? ledData : (ledData?.data ?? []);
            sensorChart.data.labels = sensorData.map(d => new Date(d.timestamp).toLocaleTimeString());
            sensorChart.data.datasets[0].data = sensorData.map(d => d.value);
            // Update label grafik sesuai nama tong
            let label = 'Ketinggian Sampah (cm)';
            if (deviceId && userDevicesCache.length > 0) {
                const device = userDevicesCache.find(d => d.id == deviceId);
                if (device && device.nama_device) {
                    label = `${device.nama_device} (cm)`;
                }
            }
            sensorChart.data.datasets[0].label = label;
            sensorChart.update();
            // Ambil status LED dari API, jika tidak ada fallback ke cache
            let ledStatus = devicesList[0]?.led_status;
            if (!ledStatus && userDevicesCache.length > 0 && deviceId) {
                const device = userDevicesCache.find(d => d.id == deviceId);
                ledStatus = device?.led_status || 'Unknown';
            }
            const ledStatusElem = document.getElementById('ledStatus');
            if (ledStatusElem) {
                ledStatusElem.textContent = ledStatus;
                ledStatusElem.className = `px-6 py-3 rounded-lg text-white font-bold text-lg shadow ${String(ledStatus).toLowerCase() === 'on' ? 'bg-green-500' : String(ledStatus).toLowerCase() === 'off' ? 'bg-red-500' : 'bg-gray-400'}`;
            }
            // Update selected device name badge
            const selectedNameEl = document.getElementById('selectedDeviceName');
            if (selectedNameEl && deviceId && userDevicesCache.length > 0) {
                const deviceObj = userDevicesCache.find(d => d.id == deviceId);
                selectedNameEl.textContent = deviceObj?.nama_device ?? '-';
            }
            ledStatusElem.className = `px-6 py-3 rounded-lg text-white font-bold text-lg shadow ${String(ledStatus).toLowerCase() === 'on' ? 'bg-green-500' : String(ledStatus).toLowerCase() === 'off' ? 'bg-red-500' : 'bg-gray-400'}`;
        }
        // Klik baris tabel untuk update grafik
        async function updateUserDeviceTable() {
            // Selalu fetch data terbaru
            const devices = await fetchUserDevices();
            const tbody = document.getElementById('userDeviceTableBody');
            tbody.innerHTML = '';
            (devices || []).forEach(device => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-[#fff7e6] transition cursor-pointer';
                if (selectedDeviceId === device.id) {
                    tr.classList.add('bg-[#fff7e6]', 'font-bold', 'ring-2', 'ring-[#f6c90e]');
                }
                
                // Status badge
                let statusBadge = '';
                if (device.status === 'pending') {
                    statusBadge = '<span class="text-yellow-500 font-semibold">Pending</span>';
                } else if (device.status === 'online') {
                    statusBadge = '<span class="text-green-600 font-semibold">Online</span>';
                } else {
                    statusBadge = '<span class="text-red-600 font-semibold">Offline</span>';
                }
                
                // Cleaning status badge
                let cleaningBadge = '';
                if (device.cleaning_status === 'sudah') {
                    cleaningBadge = '<span class="text-green-600 font-semibold">Sudah</span>';
                } else {
                    cleaningBadge = '<span class="text-red-600 font-semibold">Belum</span>';
                }
                
                const gas = device.latest_reading?.gas ?? '-';
                const volume = device.latest_reading?.volume ? device.latest_reading.volume + '%' : '-';
                
                tr.innerHTML = `
                    <td class='py-2 px-6'>${device.id}</td>
                    <td class='py-2 px-6'>${device.nama_device || '-'}</td>
                    <td class='py-2 px-6'>${device.lokasi || '-'}</td>
                    <td class='py-2 px-6'>${statusBadge}</td>
                    <td class='py-2 px-6'>${gas}</td>
                    <td class='py-2 px-6'>${volume}</td>
                    <td class='py-2 px-6'>${cleaningBadge}</td>
                `;
                tr.addEventListener('click', async () => {
                    selectedDeviceId = device.id;
                    await updateChartAndStatus(selectedDeviceId);
                    updateUserDeviceTable(); // panggil ulang agar highlight baris update
                });
                tbody.appendChild(tr);
            });
        }
        // Inisialisasi: tampilkan grafik tong pertama jika ada
        async function initDashboard() {
            console.log('Initializing dashboard...');
            const devices = await fetchUserDevices();
            console.log('Devices loaded:', devices);
            
            await updateUserDeviceTable(); // Load tabel terlebih dahulu
            
            if (devices.length > 0) {
                selectedDeviceId = devices[0].id;
                await updateChartAndStatus(selectedDeviceId);
            } else {
                await updateChartAndStatus();
            }
            
            console.log('Dashboard initialized');
        }
        
        initDashboard();
        
        // Auto-refresh data setiap 5 detik
        console.log('Setting up auto-refresh interval...');
        setInterval(async () => {
            console.log('Auto-refreshing data at', new Date().toLocaleTimeString());
            
            // Refresh tabel device dan notifikasi
            await updateUserDeviceTable();
            await refreshNotifications();
            
            // Refresh grafik device yang sedang dipilih
            if (selectedDeviceId) {
                await updateChartAndStatus(selectedDeviceId);
            }
        }, 5000);
        
        // Fungsi untuk refresh notifikasi
        async function refreshNotifications() {
            try {
                const response = await fetch('/api/dashboard/data', {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                console.log('Notification data:', data); // Debug log
                
                if (data.success) {
                    const container = document.getElementById('notifikasi-container');
                    if (data.notifikasis && data.notifikasis.length > 0) {
                        let html = '<div class="mb-6">';
                        data.notifikasis.forEach(notif => {
                            html += `
                                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2">
                                    <p><strong>Notifikasi:</strong> ${notif.keterangan}</p>
                                    <p>User: ${notif.user ? notif.user.name : '-'}</p>
                                    <p>Tong: ${notif.device ? notif.device.nama_device : '-'}</p>
                                    <p>Status: ${notif.status}</p>
                                </div>
                            `;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '';
                    }
                }
            } catch (error) {
                console.error('Error refreshing notifications:', error);
            }
        }
        
        // (hapus duplikasi fungsi updateUserDeviceTable dan fetchUserDevices di bawah ini)
    </script>
</body>
</html>
