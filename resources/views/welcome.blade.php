<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempat Sampah Pintar Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6 text-center">Monitoring Tempat Sampah Otomatis</h1>
        <div class="flex justify-center mb-8 space-x-4">
            <a href="/login/user" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Login User</a>
            <a href="/login/tukang" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Login Tukang</a>
        </div>
        <div class="bg-white rounded shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Grafik Sensor Tempat Sampah</h2>
            <canvas id="sensorChart" height="100"></canvas>
        </div>
        <div class="bg-white rounded shadow p-6 mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold">Status LED</h2>
            <span id="ledStatus" class="px-4 py-2 rounded text-white font-bold">Loading...</span>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        async function fetchSensorData() {
            // Ganti URL API sesuai endpoint Anda
            const response = await fetch('/api/sensor-readings');
            return response.json();
        }

        async function fetchLedStatus() {
            // Ganti URL API sesuai endpoint Anda
            const response = await fetch('/api/devices');
            return response.json();
        }

        const ctx = document.getElementById('sensorChart').getContext('2d');
        let sensorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Ketinggian Sampah (cm)',
                    data: [],
                    backgroundColor: 'rgba(59,130,246,0.2)',
                    borderColor: 'rgba(59,130,246,1)',
                    borderWidth: 2,
                    fill: true,
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        async function updateChartAndStatus() {
            const sensorData = await fetchSensorData();
            const ledData = await fetchLedStatus();

            // Asumsikan sensorData = [{timestamp, value}, ...]
            sensorChart.data.labels = sensorData.map(d => new Date(d.timestamp).toLocaleTimeString());
            sensorChart.data.datasets[0].data = sensorData.map(d => d.value);
            sensorChart.update();

            // Asumsikan ledData = [{led_status: 'ON'|'OFF', ...}]
            const ledStatus = ledData[0]?.led_status || 'Unknown';
            const ledStatusElem = document.getElementById('ledStatus');
            ledStatusElem.textContent = ledStatus;
            ledStatusElem.className = `px-4 py-2 rounded text-white font-bold ${ledStatus === 'ON' ? 'bg-green-500' : 'bg-red-500'}`;
        }

        updateChartAndStatus();
        setInterval(updateChartAndStatus, 5000); // update setiap 5 detik
    </script>
</body>
</html>
