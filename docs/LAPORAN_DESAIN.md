# LAPORAN DESAIN

Proyek: Smart Trash Bin — ESP32-C3
Penulis: (isi nama)
Tanggal: 2026-01-08

---

## Abstrak
Laporan ini menyajikan perancangan hardware dan software untuk sistem tong sampah pintar berbasis ESP32-C3 berdasarkan `code_program.ino`. Dokumen mencakup komponen, pinout, alur logika firmware, format komunikasi dengan server, pengujian fungsional, dan rekomendasi perbaikan untuk stabilitas, keamanan, dan skalabilitas.

## 1. Pendahuluan
Tujuan: merancang sistem otomatis yang membuka penutup saat ada tangan, mengukur persentase isi, mengeluarkan alarm jika penuh, dan mengirim data ke server.
Lingkup: perancangan hardware (komponen, koneksi) dan software (firmware, protokol komunikasi). Implementasi mengikuti kode sumber `code_program.ino`.

## 2. Komponen & Bill of Materials (BOM)
- 1x ESP32-C3 (atau varian ESP32 kompatibel)
- 2x Ultrasonic sensor (HC-SR04 atau alternatif)
- 1x Servo mikro (SG90)
- 1x Buzzer (aktif atau pasif)
- Power supply 5V (terpisah untuk servo direkomendasikan)
- Kabel jumper, konektor, breadboard/PCB
- (Opsional) Sensor gas (MQ-135) dan divider/ADC

## 3. Skematik & Pinout
Pin yang digunakan dalam kode (catatan: beberapa pin seperti GPIO0/GPIO1 dapat mengganggu proses boot pada ESP32 — pindahkan bila perlu):
- TRIG_HAND: digital 9
- ECHO_HAND: digital 8
- TRIG_VOLUME: digital 0 (perhatian: pin boot)
- ECHO_VOLUME: digital 1 (perhatian: pin boot/serial)
- SERVO_PIN: digital 4
- BUZZER_PIN: digital 2

Rekomendasi praktis:
- Ganti TRIG_VOLUME/ECHO_VOLUME ke GPIO yang tidak mengganggu boot (mis. 16/17/18) bila board Anda memakai pin 0/1 untuk UART.
- Sambungkan GND semua modul bersama.
- Power servo dari regulator 5V terpisah bila arus servo signifikan; gunakan kapasitansi peredam (100uF) dekat servo.
- Jika menggunakan HC-SR04 (5V), gunakan level shifter pada pin ECHO ke 3.3V.

## 4. Desain Software (Firmware)
Ringkasan alur:
1. Inisialisasi: Serial, pinMode, WiFi (WIFI_STA), attach servo.
2. Loop: baca sensor tangan (ultrasonic 1), baca volume (ultrasonic 2), hitung persentase volume (fungsi `calculateVolume`), baca gas (dummy/random saat ini).
3. Logika kontrol: jika volume >= 80% → set `volumeLocked=true`, aktifkan buzzer dan pastikan servo menutup. Jika volume < 80% dan tangan terdeteksi dalam jarak `OPEN_DISTANCE` → buka servo selama 3 detik lalu tutup.
4. Pengiriman data: kirim JSON via HTTP POST setiap `sendInterval` (5 detik). Format sekarang: {"device_id":6,"volume":<int>,"gas":<int>}.

State Machine:
- IDLE: pembacaan sensor berkala.
- HAND_DETECTED: buka penutup (SEQUENCE: buka -> delay(3000) -> tutup).
- FULL_LOCKED: volume >= 80% → LOCKED (buzzer ON), tolak buka.
- SENDING: sedang melakukan HTTP POST.

Kendala yang teridentifikasi:
- Penggunaan `delay()` memblokir; mengganggu responsivitas dan WiFi handling. Disarankan beralih ke non-blocking timer (millis).
- WiFi reconnect sederhana; perlu strategi reconnect dan pengecekan ulang saat gagal kirim.
- HTTP tanpa autentikasi / tanpa TLS; rawan manipulasi.

## 5. Komunikasi & API
Endpoint saat ini: `http://<serverIP>:8000/api/esp32/sensor`
Payload JSON minimal: device_id, volume, gas.
Rekomendasi: tambahkan `timestamp`, `rssi` (WiFi strength), `device_status`, dan `auth_token`. Pertimbangkan migrasi ke MQTT (topik per device) untuk koneksi berkelanjutan dan efisien.

## 6. Pengujian Fungsional
Langkah uji:
1. Uji bacaan ultrasonic dengan object terkalibrasi pada beberapa jarak (5cm sampai 50cm).
2. Uji pembukaan otomatis: gerakkan tangan pada jarak < `OPEN_DISTANCE` dan verifikasi servo membuka lalu menutup.
3. Uji kondisi penuh: set jarak volume mendekati `FULL_DISTANCE` dan verifikasi buzzer aktif dan servo tidak membuka.
4. Uji koneksi API: sediakan endpoint test (mis. httpbin.org/post) lalu verifikasi payload JSON.

Hasil pengujian (contoh):
- Bacaan ultrasonic cenderung berisik: disarankan ambil median dari 3-5 pembacaan.
- Delay selama 3000ms menyebabkan blocking; saat pengujian, pengiriman HTTP sempat tertunda.

## 7. Keamanan, Keandalan & Perbaikan
- Keamanan: gunakan HTTPS atau MQTT+TLS; tambahkan token otentikasi.
- Keandalan: gunakan retry dengan exponential backoff untuk HTTP; simpan status lokal bila gagal kirim.
- Perbaikan firmware: hilangkan `delay()` berlebih, gunakan non-blocking timers, tambahkan debouncing/median filter pada sensor.
- Tambahan fitur: OTA updates, logging ke SD/flash, status LED, konfigurasi via captive-portal.

## 8. Arsitektur Backend
Backend dibangun menggunakan **Laravel** (PHP framework) yang mengelola API, database, dan logika bisnis aplikasi IoT.

### 8.1 Struktur Backend
**Direktori utama:**
- `app/Models/`: Model data (Device, SensorReading, User, Notifikasi)
- `app/Http/Controllers/`: Controller untuk menangani request API
- `app/Http/Middleware/`: Middleware untuk autentikasi, CORS, dan validasi
- `app/Observers/`: Observer pattern untuk monitoring perubahan data (SensorReadingObserver)
- `database/migrations/`: Script perubahan struktur database
- `database/seeders/`: Data awal untuk testing
- `config/`: Konfigurasi aplikasi (auth, database, cache, queue)
- `routes/api.php`: Definisi route API

### 8.2 Model Data Backend
**User**: Pengguna sistem (admin, operator)
- id, name, email, password, role, alamat, nomor_telepon, profile_photo
- Role-based access control (admin/operator/viewer)

**Device**: Perangkat ESP32/Tong Pintar
- id, device_id, device_name, location, status (online/offline), led_status
- Relasi: many SensorReadings, many Notifications

**SensorReading**: Data pembacaan sensor
- id, device_id, volume (%), gas (ppm), humidity, temperature, timestamp
- Otomatis dicatat via API POST dari ESP32
- Observer mendeteksi saat volume >= 80% → trigger notifikasi

**Notifikasi**: Alert dan log kejadian
- id, device_id, type (FULL_TRASH, ERROR, STATUS_CHANGE), message, is_read, created_at

### 8.3 API Endpoints Backend
**Authentication:**
- `POST /api/login` — Login user (email, password)
- `POST /api/logout` — Logout dan invalidate token
- `POST /api/register` — Register akun baru (admin only)
- `GET /api/user/profile` — Ambil profil user yang login

**Device Management:**
- `GET /api/devices` — List semua device
- `GET /api/devices/{id}` — Detail device
- `POST /api/devices` — Tambah device baru (admin only)
- `PUT /api/devices/{id}` — Update device info
- `DELETE /api/devices/{id}` — Hapus device (admin only)
- `PUT /api/devices/{id}/led` — Kontrol LED on/off

**Sensor Data:**
- `POST /api/esp32/sensor` — Terima data sensor dari ESP32 (no auth untuk IoT)
- `GET /api/sensors/readings` — List pembacaan sensor dengan filter (device, date range)
- `GET /api/sensors/readings/{id}` — Detail pembacaan
- `GET /api/sensors/stats` — Statistik volume, gas, trend

**Notifications:**
- `GET /api/notifications` — List notifikasi user
- `GET /api/notifications/unread` — Hitung notifikasi belum dibaca
- `PUT /api/notifications/{id}/read` — Mark as read
- `DELETE /api/notifications/{id}` — Hapus notifikasi

### 8.4 Database Schema (Migrasi)
**users table:**
```
id, name, email, password, role (admin|operator|viewer), 
alamat, nomor_telepon, profile_photo, created_at, updated_at
```

**devices table:**
```
id, device_id, device_name, location, status (online|offline), 
led_status (on|off), created_at, updated_at
```

**sensor_readings table:**
```
id, device_id (FK), volume (int 0-100), gas (int), 
humidity (nullable), temperature (nullable), timestamp, created_at
```

**notifikasi table:**
```
id, device_id (FK), type (FULL_TRASH|ERROR|STATUS_CHANGE), 
message, is_read, created_at, updated_at
```

### 8.5 Keamanan Backend
- Autentikasi: Sanctum API Token atau JWT
- Rate limiting: Throttle API requests per IP/user
- CORS: Hanya allow origin dari frontend domain
- Input validation: Request validation via FormRequest classes
- SQL injection protection: Eloquent ORM dengan parameterized queries
- HTTPS: Enforce SSL certificate untuk production

---

## 9. Arsitektur Frontend
Frontend adalah aplikasi web interaktif untuk monitoring dan kontrol sistem tong pintar secara real-time.

### 9.1 Teknologi Frontend
- **Framework**: Vue.js 3 atau React (via Vite)
- **Build tool**: Vite.js (live reload, code splitting)
- **Styling**: Tailwind CSS (utility-first CSS framework)
- **State management**: Pinia (Vue) atau Context API (React)
- **HTTP client**: Axios
- **UI Components**: Custom components atau library (Vue Material, Headless UI)

### 9.2 Struktur Frontend
**Direktori sumber:**
- `resources/js/`: Kode JavaScript/Vue/React utama
  - `pages/`: Halaman aplikasi (Login, Dashboard, Devices, History, Settings)
  - `components/`: Komponen reusable (Card, Button, Modal, Chart)
  - `stores/` atau `context/`: State management
  - `api/`: Service untuk komunikasi HTTP ke backend
- `resources/css/`: Stylesheet (app.css untuk Tailwind)
- `resources/views/`: Template HTML entry (index.html melalui Vite)

### 9.3 Fitur Frontend Utama

**1. Halaman Login/Register**
- Form email & password
- Validasi input client-side
- Simpan token di localStorage/sessionStorage
- Redirect ke dashboard jika sudah login

**2. Dashboard (Home)**
- Grid/card untuk setiap device dengan status live:
  - Device name, location, current volume (%), last update
  - Indikator status (online/offline, LED on/off)
  - Tombol kontrol LED
  - Chart mini volume trend 24 jam terakhir
- Overview stats: Total device, device online, trash penuh, notifikasi unread
- Notifikasi bell/panel dengan list alert terbaru

**3. Detail Device**
- Informasi lengkap device (ID, location, status, uptime)
- Real-time gauge/indicator: volume (%), gas (ppm)
- Chart history: volume, gas, humidity, temperature (line/area chart)
- Filter: date range, resolution (1h, 1d)
- Control panel: LED on/off, force refresh sensor
- Log kejadian / events timeline

**4. Halaman History/Analytics**
- Tabel pembacaan sensor dengan pagination, sorting, filter
- Export data ke CSV/Excel
- Advanced analytics: moving average, peak detection, anomaly
- Predictive: estimasi kapan trash penuh berdasarkan trend

**5. Manajemen Device (Admin)**
- CRUD device: tambah, edit, hapus
- Bulk actions: edit multiple devices
- Device groups/categories

**6. Notifikasi & Alert**
- Pop-up/toast untuk real-time alert (volume penuh, device offline)
- Notifikasi center: list, filter, mark as read, delete
- Notification preferences: email alert, SMS (opsional)

**7. User Management (Admin)**
- List user dengan role
- Tambah/edit/hapus user
- Change password, reset password
- Activity log per user

**8. Settings & Profil**
- Edit profil user (nama, email, foto)
- Ganti password
- Preferensi notifikasi
- Dark mode toggle

### 9.4 Integrasi Frontend-Backend
**API Client Service (api.js / axios.ts):**
```javascript
const api = axios.create({ baseURL: 'http://localhost:8000/api' })

api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export const fetchDevices = () => api.get('/devices')
export const fetchSensorReadings = (deviceId) => 
  api.get(`/sensors/readings?device_id=${deviceId}`)
export const updateLED = (deviceId, status) => 
  api.put(`/devices/${deviceId}/led`, { status })
```

**Real-time Updates (Optional - WebSocket/Server-Sent Events):**
```javascript
const eventSource = new EventSource(
  '/api/devices/stream?token=' + token
)
eventSource.addEventListener('sensor_update', e => {
  // Update UI dengan data baru
})
```

### 9.5 User Experience & Responsive Design
- Mobile-first design: layout responsive untuk smartphone, tablet, desktop
- Light/Dark mode support
- Accessibility: WCAG compliance (colors, contrast, keyboard nav)
- Performance: lazy loading, code splitting, image optimization
- Loading states: skeleton screens, spinners, progress bar
- Error handling: user-friendly error messages

### 9.6 Build & Deployment Frontend
**Development:**
```bash
npm install
npm run dev  # Vite dev server di http://localhost:5173
```

**Production:**
```bash
npm run build  # Generate dist/ folder
# Serve dist/ via web server (nginx, Apache, atau Laravel public/)
```

---

## 10. Rekomendasi untuk Laporan Praktikum / Tugas Akhir
- Sertakan skematik gambar breadboard atau PCB, dan foto susunan nyata.
- Lampirkan potongan kode kunci:
  - Firmware: fungsi pembacaan ultrasonic, `calculateVolume`, `sendDataToAPI`, dan logika buka/tutup
  - Backend: model User/Device, API endpoints, migration database
  - Frontend: komponen Dashboard, service API client, state management
- Berikan hasil pengukuran tabel jarak vs pembacaan mentah dan persentase volume.
- Analisis masalah: konsumsi daya, akurasi ultrasonic dalam lingkungan tertentu, masalah boot saat menggunakan GPIO0/1.
## 11. Kesimpulan
Sistem Smart Trash Bin (Tong Sampah Pintar) terdiri dari tiga lapisan terintegrasi:

1. **Hardware (Firmware ESP32-C3)**: Menangkap sensor (ultrasonic, buzzer, servo), logika deteksi tangan, pengukuran volume, dan pengiriman data via HTTP ke backend.

2. **Backend (Laravel API)**: Menyimpan data sensor, mengelola device inventory, user authentication, logic notifikasi otomatis (alert saat penuh), dan menyediakan REST API untuk frontend.

3. **Frontend (Vue.js + Tailwind CSS)**: Interface interaktif untuk real-time monitoring volume trash, kontrol LED, history analytics, manajemen device, dan notifikasi alert untuk pengguna.

Desain dasar sudah berfungsi end-to-end: deteksi tangan, pengukuran volume, aksi servo, penyimpanan data di database, dan visualisasi via web. Untuk penggunaan lapangan dan produksi, diperlukan perbaikan pada:
- Pinout firmware (hindari GPIO0/1 yang mengganggu boot)
- Komunikasi aman (HTTPS/MQTT+TLS, token autentikasi)
- Pengelolaan daya (low-power mode, WiFi sleep)
- Peningkatan keandalan (debouncing sensor, retry logic, offline-first sync)
- Skalabilitas backend (caching, message queue, database indexing)
- UX frontend (offline mode, PWA, push notifications)

---

## Lampiran A — JSON Contoh Payload ESP32 → Backend
```json
{
  "device_id": 6,
  "volume": 42,
  "gas": 12,
  "timestamp": "2026-01-08T10:00:00Z",
  "rssi": -60
}
```

## Lampiran B — Contoh Response API Backend
**GET /api/devices (list device):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "device_id": 6,
      "device_name": "Trash Bin Lobby",
      "location": "Building A, Ground Floor",
      "status": "online",
      "led_status": "on",
      "last_reading": {
        "volume": 42,
        "gas": 12,
        "timestamp": "2026-01-10T14:30:00Z"
      }
    }
  ]
}
```

**GET /api/sensors/readings (history pembacaan):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 101,
      "device_id": 6,
      "volume": 40,
      "gas": 10,
      "humidity": 65,
      "temperature": 28.5,
      "timestamp": "2026-01-10T14:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "per_page": 20
  }
}
```

## Lampiran C — Contoh Frontend State (Pinia/Vuex)
```javascript
const deviceStore = defineStore('device', {
  state: () => ({
    devices: [],
    selectedDevice: null,
    notifications: [],
    isLoading: false,
    error: null
  }),
  
  actions: {
    async fetchDevices() {
      this.isLoading = true
      try {
        const res = await api.get('/devices')
        this.devices = res.data.data
      } catch (e) {
        this.error = e.message
      } finally {
        this.isLoading = false
      }
    },
    
    async updateLED(deviceId, status) {
      await api.put(`/devices/${deviceId}/led`, { status })
      const device = this.devices.find(d => d.id === deviceId)
      if (device) device.led_status = status
    }
  }
})
```

## Lampiran D — Struktur Direktori Lengkap Proyek
```
tmpt_smph_pintar/
├── code_program.ino              # Firmware ESP32
├── package.json / composer.json  # Dependencies
├── vite.config.js                # Vite config untuk frontend
├── tailwind.config.js            # Tailwind CSS config
├── postcss.config.js             # PostCSS setup
│
├── app/                          # Backend Laravel (PHP)
│   ├── Models/
│   │   ├── User.php
│   │   ├── Device.php
│   │   ├── SensorReading.php
│   │   └── Notifikasi.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DeviceController.php
│   │   │   ├── SensorController.php
│   │   │   └── NotificationController.php
│   │   └── Middleware/
│   ├── Observers/
│   │   └── SensorReadingObserver.php
│   └── Providers/
│
├── database/                     # Database Laravel
│   ├── migrations/               # SQL schema
│   └── seeders/
│
├── routes/
│   ├── api.php                   # API routes
│   └── web.php                   # Web routes
│
├── resources/                    # Frontend assets
│   ├── js/
│   │   ├── pages/               # Vue pages
│   │   │   ├── LoginPage.vue
│   │   │   ├── DashboardPage.vue
│   │   │   ├── DeviceDetailPage.vue
│   │   │   └── HistoryPage.vue
│   │   ├── components/          # Reusable components
│   │   │   ├── DeviceCard.vue
│   │   │   ├── Chart.vue
│   │   │   └── NotificationBell.vue
│   │   ├── stores/              # Pinia stores
│   │   │   ├── deviceStore.js
│   │   │   ├── userStore.js
│   │   │   └── notificationStore.js
│   │   ├── api/
│   │   │   └── client.js        # Axios API client
│   │   └── App.vue
│   ├── css/
│   │   └── app.css              # Tailwind globals
│   └── views/
│       └── app.blade.php        # Blade template
│
├── docs/                        # Dokumentasi
│   ├── LAPORAN_DESAIN.md       # File ini
│   ├── PERANCANGAN_LAPORAN.md
│   └── DESAIN.md
│
├── config/                      # Laravel config
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   └── ... (lainnya)
│
├── public/                      # Public assets
│   ├── index.php                # Entry point
│   ├── build/                   # Vite build output (dist/)
│   └── ... (static files)
│
└── vendor/                      # Dependencies (PHP packages)
```

## Lampiran E — Tech Stack Summary
| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Hardware** | ESP32-C3, Ultrasonic, Servo, Buzzer | Sensing, actuation, control |
| **Firmware** | C++ (Arduino IDE) | Embedded logic, WiFi, HTTP client |
| **Backend** | Laravel, PHP | REST API, database, business logic |
| **Frontend** | Vue.js 3, Vite, Tailwind CSS | Web UI, real-time monitoring |
| **Database** | MySQL/PostgreSQL | Persistent storage (users, devices, readings) |
| **Deployment** | Docker (optional), Linux Server | Production environment |

## Lampiran F — Referensi & Resources
- `code_program.ino` (firmware source)
- Dokumentasi ESP32: https://docs.espressif.com/
- Laravel Docs: https://laravel.com/docs
- Vue.js 3: https://vuejs.org/
- Vite: https://vitejs.dev/
- Tailwind CSS: https://tailwindcss.com/
- HC-SR04 Ultrasonic Sensor datasheet
- Sanctum API (Laravel): https://laravel.com/docs/sanctum
- Pinia State Management: https://pinia.vuejs.org/

---

Jika Anda mau, saya bisa:
- Menambahkan skematik breadboard (PNG/SVG) di `docs/diagrams/`;
- Mengonversi laporan ini ke PDF;
- Memperbarui kode untuk pindah pin, non-blocking timers, atau mengganti HTTP→MQTT.
- Membuat contoh implementasi lengkap controller/model backend atau component frontend.
