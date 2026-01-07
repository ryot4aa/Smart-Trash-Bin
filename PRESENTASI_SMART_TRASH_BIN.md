# SMART TRASH BIN MONITORING SYSTEM
## Dokumentasi Lengkap untuk Presentasi

---

## 1. SENSOR DAN ACTUATOR YANG DIGUNAKAN

### A. SENSOR (Input Devices)

#### 1. Ultrasonic Sensor HC-SR04 (Quantity: 2)
**Sensor 1: Pengukur Volume Sampah**
- **Fungsi**: Mengukur jarak dari permukaan sampah ke ujung tong
- **Range Pengukuran**: 2cm - 400cm
- **Akurasi**: ±3mm
- **Output Signal**: PWM (Pulse Width Modulation)
- **Konversi ke Persentase Volume**:
  - Jarak 0-30cm = 100% - 0% volume
  - Formula: `volume = map(distance, 0, 30, 100, 0)`
  - Ketika volume ≥ 80% → Buzzer ON + Notifikasi

**Sensor 2: Deteksi Buka Tutup Otomatis**
- **Fungsi**: Mendeteksi tangan/objek di dekat tong
- **Trigger Distance**: < 20cm
- **Aksi**: Trigger servo motor buka tutup tutup

#### 2. Gas Sensor MQ-2 / MQ-135
- **Fungsi**: Mendeteksi kadar gas berbahaya dalam tong
- **Jenis Gas yang Terdeteksi**: 
  - LPG (Liquefied Petroleum Gas)
  - Alkohol
  - Asap
  - Hidrogen Sulfida
  - Ammonia
- **Output**: Analog (0-5V) / ADC (0-4095 pada ESP32)
- **Konversi**: 
  - Raw value 0-4095 dipetakan ke 0-100 ppm
  - Threshold alarm: ≥ 100 ppm
  - Saat threshold terlampaui → Notifikasi "Gas Berbahaya"

---

### B. ACTUATOR (Output Devices)

#### 1. Buzzer / LED Status (Kolom: led_status)
- **Fungsi**: Alarm audio/visual ketika tong penuh (≥80%)
- **Status**: ON / OFF
- **Kontrol**: Via database kolom `devices.led_status`
- **Update Otomatis**: SensorReadingObserver
  - Jika volume ≥ 80% → led_status = 'on'
  - Jika volume < 80% → led_status = 'off'

#### 2. Servo Motor (Estimasi)
- **Fungsi**: Buka/tutup lid tong secara otomatis
- **Trigger**: Deteksi ultrasonic jarak dekat (<20cm)
- **Angle**:
  - 0° = Tertutup
  - 90° = Terbuka

---

## 2. CARA KERJA SENSOR & ACTUATOR DENGAN MONITORING & KONTROL

### Alur Kerja Sistem:

```
┌─────────────────────────────────────────────────────────────────┐
│                    ESP32 IoT Device                             │
│                  (Microcontroller)                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────┐  │
│  │ Ultrasonic Sensor│  │ Ultrasonic Sensor│  │ Gas Sensor   │  │
│  │ (Volume Reading) │  │ (Hand Detection) │  │ (MQ-2/135)   │  │
│  │ GPIO 15, 4       │  │ GPIO 13, 12      │  │ GPIO 34 (ADC)│  │
│  └────────┬─────────┘  └────────┬─────────┘  └──────┬───────┘  │
│           │                     │                   │            │
│           └─────────────┬───────┴───────────────────┘            │
│                         │                                        │
│                 ┌───────▼──────────┐                             │
│                 │ ADC & GPIO Read  │                             │
│                 │ Pembacaan Data   │                             │
│                 └────────┬─────────┘                             │
│                          │                                       │
│         ┌────────────────┼────────────────┐                      │
│         │                │                │                      │
│    ┌────▼─────┐   ┌─────▼──────┐  ┌─────▼──────┐                │
│    │ Volume   │   │ Hand       │  │ Gas Level  │                │
│    │ 0-100%   │   │ Detected?  │  │ 0-100 ppm  │                │
│    └────┬─────┘   └─────┬──────┘  └─────┬──────┘                │
│         │                │              │                        │
│         └────────────────┼──────────────┘                        │
│                          │                                       │
│         ┌────────────────▼──────────────┐                        │
│         │  HTTP POST Request            │                        │
│         │  Content-Type: application/json
│         │  URL: http://IP:PORT/api/esp32/sensor
│         │  Body: {                      │                        │
│         │    "device_id": 6,           │                        │
│         │    "volume": 75,             │                        │
│         │    "gas": 20                 │                        │
│         │  }                            │                        │
│         └────────────┬──────────────────┘                        │
│                      │                                           │
└──────────────────────┼───────────────────────────────────────┘
                       │
                       │ KIRIM DATA SENSOR
                       │
        ┌──────────────▼────────────────────┐
        │    Web Server (Laravel)           │
        │    192.168.1.5:8000              │
        │    /api/esp32/sensor             │
        └──────────────┬────────────────────┘
                       │
        ┌──────────────▼────────────────────┐
        │   Validasi & Proses Request       │
        ├──────────────────────────────────┤
        │ - Check device_id ada di database│
        │ - Validasi volume & gas range    │
        │ - Simpan ke tabel sensor_readings│
        │ - Update device status = 'online'│
        └──────────────┬────────────────────┘
                       │
        ┌──────────────▼────────────────────┐
        │ SensorReadingObserver TRIGGER     │
        │ (Otomasi Bisnis Logic)           │
        ├──────────────────────────────────┤
        │ Cek Volume:                       │
        │ IF volume >= 80% THEN            │
        │   - devices.led_status = 'on'    │
        │   - Create notifikasi 'penuh'    │
        │ ELSE                              │
        │   - devices.led_status = 'off'   │
        │   - Mark notifikasi 'penuh' read │
        │                                   │
        │ Cek Gas:                          │
        │ IF gas >= 100 THEN               │
        │   - Create notifikasi 'gas_bahaya'
        │ ELSE                              │
        │   - Mark notifikasi 'gas' read   │
        └──────────────┬────────────────────┘
                       │
        ┌──────────────▼────────────────────┐
        │  Database (MySQL)                 │
        │ ┌──────────────────────────────┐ │
        │ │ sensor_readings (INSERT)     │ │
        │ │ devices (UPDATE status/LED)  │ │
        │ │ notifikasi (INSERT/UPDATE)   │ │
        │ └──────────────────────────────┘ │
        └──────────────┬────────────────────┘
                       │
        ┌──────────────▼────────────────────┐
        │  Dashboard User (Browser)         │
        │  Auto-refresh 5 detik via:        │
        │  /api/dashboard/data              │
        │  /api/my-devices                  │
        │  /api/sensor-readings             │
        ├──────────────────────────────────┤
        │ ✓ Tampil Notifikasi Real-time    │
        │ ✓ Update Tabel Device Status     │
        │ ✓ Update Grafik Volume           │
        │ ✓ Status LED/Buzzer              │
        │ ✓ Cleaning Status                │
        └──────────────────────────────────┘
```

### Penjelasan Alur Kerja:

1. **Pembacaan Sensor (ESP32)**
   - Ultrasonic 1 membaca jarak → convert ke volume %
   - Ultrasonic 2 mendeteksi tangan (trigger servo)
   - Gas sensor membaca analog value → convert ke ppm

2. **Pengiriman Data**
   - ESP32 kirim HTTP POST ke `/api/esp32/sensor`
   - Device_id, volume, gas dalam JSON body
   - Kirim setiap 5-10 detik

3. **Proses di Server**
   - Laravel menerima request POST
   - Validasi device_id & data range
   - Simpan ke tabel `sensor_readings`
   - Update `devices.status = 'online'`

4. **Trigger Otomatis (Observer)**
   - Setiap INSERT sensor_reading → Observer dijalankan
   - Cek threshold volume & gas
   - Jika kondisi terpenuhi → SET buzzer & CREATE notif
   - Jika kondisi normal kembali → UNSET buzzer & MARK notif read

5. **Tampilan di Dashboard**
   - JavaScript auto-fetch `/api/dashboard/data` setiap 5 detik
   - Update tabel, grafik, notifikasi real-time
   - User lihat status buzzer, volume, gas tanpa refresh

---

## 3. VISUALISASI DATA: DARI DB KE APLIKASI

### Data Flow Lengkap:

```
┌───────────────────────────────────────────────────────────────┐
│           DATABASE (MySQL) - Penyimpanan Data                 │
├───────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────┐  ┌──────────────┐  ┌──────────────────┐  │
│  │ sensor_readings │  │  devices     │  │ notifikasi       │  │
│  │ (raw data)      │  │ (status/LED) │  │ (alert/info)     │  │
│  │                 │  │              │  │                  │  │
│  │ volume: 75%     │  │ status: on   │  │ status: penuh    │  │
│  │ gas: 20 ppm     │  │ led_status:  │  │ is_read: false   │  │
│  │ reading_time    │  │ on           │  │ user_id: 1       │  │
│  └────────┬────────┘  └──────┬───────┘  └────────┬─────────┘  │
└───────────┼────────────────────┼──────────────────┼────────────┘
            │                    │                  │
            │  ┌─────────────────▼──────────────────▼──────────┐
            │  │  API Endpoints (REST)                        │
            │  ├──────────────────────────────────────────────┤
            │  │                                              │
            │  │ GET /api/dashboard/data                      │
            │  │ → Devices + Notifikasi real-time            │
            │  │                                              │
            │  │ GET /api/my-devices                          │
            │  │ → Daftar tong + latest reading              │
            │  │                                              │
            │  │ GET /api/sensor-readings?device_id=1        │
            │  │ → Historical data untuk grafik              │
            │  │                                              │
            └──┬───────────────────────────────────────────────┘
               │
    ┌──────────▼─────────────────────────────────────────────────┐
    │        Browser / Frontend (JavaScript + HTML/CSS)          │
    ├────────────────────────────────────────────────────────────┤
    │                                                              │
    │  ┌─────────────────────────────────────────────────────┐   │
    │  │  NOTIFIKASI CONTAINER                              │   │
    │  │  (Update otomatis dari /api/dashboard/data)        │   │
    │  │  ┌────────────────────────────────────────────┐    │   │
    │  │  │ ⚠️  NOTIFIKASI                           │    │   │
    │  │  │ Tong sampah sudah penuh                  │    │   │
    │  │  │ User: user (John Doe)                    │    │   │
    │  │  │ Tong: Tong 1                             │    │   │
    │  │  │ Status: penuh                            │    │   │
    │  │  └────────────────────────────────────────────┘    │   │
    │  │                                                      │   │
    │  │  [Notif otomatis hilang saat volume < 80%]         │   │
    │  └─────────────────────────────────────────────────────┘   │
    │                                                              │
    │  ┌─────────────────────────────────────────────────────┐   │
    │  │  TABEL DAFTAR TONG (Auto-Update setiap 5 detik)   │   │
    │  │ ┌────┬──────────┬────────┬─────────┬─────┬─────┐   │   │
    │  │ │ ID │ Nama     │ Lokasi │ Status  │ Gas │ Vol │   │   │
    │  │ ├────┼──────────┼────────┼─────────┼─────┼─────┤   │   │
    │  │ │ 6  │ Tong 1   │ Depan  │ 🟢 ON   │ 20  │ 75% │   │   │
    │  │ │ 7  │ Tong 2   │ Belakang│ 🔴 OFF  │ -   │ 45% │   │   │
    │  │ └────┴──────────┴────────┴─────────┴─────┴─────┘   │   │
    │  │                                                      │   │
    │  │  [Klik baris untuk select device → update grafik]  │   │
    │  └─────────────────────────────────────────────────────┘   │
    │                                                              │
    │  ┌─────────────────────────────────────────────────────┐   │
    │  │  GRAFIK REAL-TIME (Chart.js)                       │   │
    │  │  [Line Chart - Ketinggian Sampah (cm)]            │   │
    │  │                                                      │   │
    │  │      │ Tong 1 (selected)                           │   │
    │  │   100├──────────────────────                       │   │
    │  │      │          ╱╲                                  │   │
    │  │    50├─────────╱  ╲─────────                       │   │
    │  │      │        ╱    ╲                                │   │
    │  │    0 ├───────────────────────→ Time                │   │
    │  │      └10:00  10:30  11:00  11:30                  │   │
    │  │                                                      │   │
    │  │  [Update otomatis dari /api/sensor-readings]      │   │
    │  └─────────────────────────────────────────────────────┘   │
    │                                                              │
    │  ┌─────────────────────────────────────────────────────┐   │
    │  │  STATUS LED / BUZZER                               │   │
    │  │                                                      │   │
    │  │  Buzzer Status: [🟢 ON]                            │   │
    │  │  (Berubah otomatis saat volume ≥80%)              │   │
    │  └─────────────────────────────────────────────────────┘   │
    │                                                              │
    │  ✅ Auto-refresh JavaScript setiap 5 detik                 │
    │  ✅ Tanpa perlu refresh halaman manual                     │
    │  ✅ Data selalu up-to-date dari database                   │
    │                                                              │
    └────────────────────────────────────────────────────────────┘
```

### Teknologi Visualisasi:

| Komponen | Teknologi | Update | Sumber Data |
|----------|-----------|--------|------------|
| Notifikasi | HTML + CSS (Alert Box) | Real-time 5s | `/api/dashboard/data` |
| Tabel | HTML Table + JavaScript | Real-time 5s | `/api/my-devices` |
| Grafik | Chart.js Library | Real-time 5s | `/api/sensor-readings` |
| Status LED | HTML Badge + CSS | Real-time 5s | `/api/my-devices` |

---

## 4. STRUKTUR DATABASE (TRD - Table Relationship Diagram)

### Tabel & Relasi:

```
┌────────────────────────────────────────────────────────────────┐
│                    USERS (Pengguna)                            │
├────────────────────────────────────────────────────────────────┤
│ PK: id (int, auto_increment)                                   │
│ ├─ name (varchar 255)                 [Nama lengkap user]     │
│ ├─ email (varchar 255, UNIQUE)        [Email login]           │
│ ├─ password (varchar 255)              [Hash bcrypt]          │
│ ├─ role (enum: 'user', 'tukang', 'admin')  [Role privilege]  │
│ ├─ alamat (text, nullable)             [Alamat rumah/kantor]  │
│ ├─ nomor_telepon (varchar 30, nullable)    [Kontak]          │
│ ├─ profile_photo (varchar, nullable)   [Path foto profil]     │
│ ├─ created_at (timestamp)              [Waktu buat]           │
│ └─ updated_at (timestamp)              [Waktu update]         │
│                                                                 │
│ Contoh Data:                                                    │
│ id=1, name='John Doe', email='john@example.com',              │
│ role='user', alamat='Jl. Contoh No. 1'                        │
└────────────┬────────────────────────────────────────────────┬──┘
             │ 1:N (One user has many devices)              │
    ┌────────▼──────────────────────────────────────┐        │
    │         DEVICES (Tong Sampah)                 │        │
    ├───────────────────────────────────────────────┤        │
    │ PK: id (int, auto_increment)                  │        │
    │ FK: user_id (int) →── USERS.id              │        │
    │ ├─ nama_device (varchar 255)   [Nama tong]   │        │
    │ ├─ lokasi (varchar 255)        [Lokasi tong] │        │
    │ ├─ tipe (varchar 255)          [Tipe device] │        │
    │ ├─ status (enum:               [Status unit] │        │
    │ │    'pending'/'online'/'offline')           │        │
    │ ├─ battery (int 0-100, nullable) [Level batt]│        │
    │ ├─ led_status (enum: 'on'/'off')  [Buzzer]   │        │
    │ ├─ cleaning_status (enum:         [Pembersih] │        │
    │ │    'sudah'/'belum')                        │        │
    │ ├─ created_at (timestamp)      [Waktu buat]  │        │
    │ └─ updated_at (timestamp)      [Waktu update]│        │
    │                                               │        │
    │ Contoh Data:                                  │        │
    │ id=6, user_id=1, nama_device='Tong 1',       │        │
    │ lokasi='Depan Rumah', status='online',       │        │
    │ led_status='off', cleaning_status='belum'    │        │
    └────────┬──────────────────────────────────────┘        │
             │ 1:N (One device has many readings)             │
    ┌────────▼──────────────────────────────────────┐        │
    │    SENSOR_READINGS (Data Sensor)              │        │
    ├───────────────────────────────────────────────┤        │
    │ PK: id (int, auto_increment)                  │        │
    │ FK: device_id (int) →── DEVICES.id          │        │
    │ ├─ volume (int 0-100)        [% kepenuhan]   │        │
    │ ├─ gas (int 0-100)           [ppm gas]      │        │
    │ ├─ reading_time (datetime)   [Waktu baca]    │        │
    │ ├─ created_at (timestamp)    [Waktu record]  │        │
    │ └─ updated_at (timestamp)    [Waktu update]  │        │
    │                                               │        │
    │ Contoh Data:                                  │        │
    │ id=51, device_id=6, volume=75, gas=20,      │        │
    │ reading_time='2026-01-06 11:00:00'          │        │
    └───────────────────────────────────────────────┘        │
                                                              │
    ┌────────────────────────────────────────────────┐        │
    │      NOTIFIKASI (Alert Otomatis)              │        │
    ├────────────────────────────────────────────────┤        │
    │ PK: id (int, auto_increment)                  │        │
    │ FK: user_id (int) →── USERS.id              │        │
    │ FK: device_id (int) →── DEVICES.id         │        │
    │ ├─ keterangan (text)         [Pesan notif]   │        │
    │ ├─ status (enum:             [Tipe alert]    │        │
    │ │    'penuh'/'gas_berbahaya')                │        │
    │ ├─ is_read (boolean)         [Sudah dibaca?] │        │
    │ ├─ created_at (timestamp)    [Waktu buat]    │        │
    │ └─ updated_at (timestamp)    [Waktu update]  │        │
    │                                               │        │
    │ Contoh Data:                                  │        │
    │ id=1, user_id=1, device_id=6,                │        │
    │ keterangan='Tong sampah sudah penuh',        │        │
    │ status='penuh', is_read=0                    │        │
    └────────────────────────────────────────────────┘        │
                                                              │
    ┌──────────────────────────────────────────────────┐     │
    │  PERSONAL_ACCESS_TOKENS (Sanctum - API Auth)    │     │
    ├──────────────────────────────────────────────────┤     │
    │ PK: id (int)                                     │     │
    │ FK: tokenable_id (int) →── USERS.id            │     │
    │ ├─ name (varchar 255)    [Nama token]          │     │
    │ ├─ token (text, unique)  [Token hash]          │     │
    │ └─ abilities (json)      [Izin API]            │     │
    │                                                  │     │
    │ [Untuk autentikasi API Sanctum bearer token]   │     │
    └──────────────────────────────────────────────────┘     │
```

### Relationship Summary:

| Relasi | Dari | Ke | Kardinalitas | Deskripsi |
|--------|------|----|----|-----------|
| owns | USERS | DEVICES | 1:N | Satu user memiliki banyak tong |
| has | DEVICES | SENSOR_READINGS | 1:N | Satu tong memiliki banyak reading |
| receives | USERS | NOTIFIKASI | 1:N | Satu user menerima banyak notif |
| belongs_to | NOTIFIKASI | DEVICES | N:1 | Banyak notif untuk satu tong |
| tokens | USERS | PERSONAL_ACCESS_TOKENS | 1:N | Satu user bisa punya banyak token |

---

## 5. JUMLAH API YANG DIKEMBANGKAN

### API Summary:

**Total: 18 Endpoints**

```
┌─────────────────────────────────────────────────────────┐
│         API ENDPOINTS BREAKDOWN                         │
├─────────┬─────────┬─────────────────────────────────────┤
│Category │ Count   │ Endpoints                           │
├─────────┼─────────┼─────────────────────────────────────┤
│Auth     │   4     │ register, login, logout, me         │
│Device   │   5     │ CRUD + control                      │
│Sensor   │   4     │ readings CRUD + latest              │
│ESP32    │   2     │ test, sensor data receive           │
│Dashboard│   3     │ dashboard data, devices, readings   │
├─────────┼─────────┼─────────────────────────────────────┤
│TOTAL    │  18     │                                     │
└─────────┴─────────┴─────────────────────────────────────┘
```

### Daftar Lengkap:

| No | Method | Endpoint | Kategori | Autentikasi |
|----|--------|----------|----------|------------|
| 1 | POST | /api/register | Auth | ❌ |
| 2 | POST | /api/login | Auth | ❌ |
| 3 | POST | /api/logout | Auth | ✅ Sanctum |
| 4 | GET | /api/me | Auth | ✅ Sanctum |
| 5 | GET | /api/devices | Device | ✅ Sanctum |
| 6 | POST | /api/devices | Device | ✅ Session/Sanctum |
| 7 | GET | /api/devices/{id} | Device | ✅ Sanctum |
| 8 | PUT | /api/devices/{id} | Device | ✅ Sanctum |
| 9 | DELETE | /api/devices/{id} | Device | ✅ Sanctum |
| 10 | POST | /api/devices/{id}/readings | Sensor | ✅ Sanctum |
| 11 | GET | /api/devices/{id}/readings | Sensor | ✅ Sanctum |
| 12 | GET | /api/devices/{id}/readings/latest | Sensor | ✅ Sanctum |
| 13 | DELETE | /api/devices/{id}/readings/{id} | Sensor | ✅ Sanctum |
| 14 | POST | /api/esp32/test | ESP32 | ❌ (Public) |
| 15 | POST | /api/esp32/sensor | ESP32 | ❌ (Public) |
| 16 | GET | /api/dashboard/data | Dashboard | ✅ Session |
| 17 | GET | /api/my-devices | Dashboard | ✅ Session |
| 18 | GET | /api/sensor-readings | Dashboard | ✅ Session |

---

## 6. PENJELASAN DETAIL SETIAP API

### KATEGORI: AUTHENTICATION (4 API)

#### API #1: POST /api/register
**Tujuan**: Registrasi user baru (User, Tukang, atau Admin)

**Request JSON**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123"
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Register berhasil!",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2026-01-06T10:30:00.000Z"
  }
}
```

**Error Response (422)**:
```json
{
  "message": "Email sudah terdaftar",
  "errors": { "email": ["Email sudah terdaftar"] }
}
```

---

#### API #2: POST /api/login
**Tujuan**: Login user dan buat session

**Request JSON**:
```json
{
  "email": "john@example.com",
  "password": "secret123"
}
```

**Response**: Redirect ke dashboard sesuai role
- `role: 'user'` → `/dashboard/user`
- `role: 'tukang'` → `/dashboard/tukang`
- `role: 'admin'` → `/dashboard/admin`

**Session Cookie**: XSRF-TOKEN + Laravel session

---

#### API #3: POST /api/logout
**Tujuan**: Logout dan hapus session

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

---

#### API #4: GET /api/me
**Tujuan**: Dapatkan profil user yang sedang login

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "alamat": "Jl. Contoh No. 1",
    "nomor_telepon": "081234567890",
    "profile_photo": "profile_photos/photo.jpg"
  }
}
```

---

### KATEGORI: DEVICE CRUD (5 API)

#### API #5: GET /api/devices
**Tujuan**: Ambil daftar semua devices user

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 6,
      "user_id": 1,
      "nama_device": "Tong 1",
      "lokasi": "Depan Rumah",
      "tipe": "smartbin",
      "status": "online",
      "battery": 95,
      "led_status": "off",
      "cleaning_status": "belum",
      "latest_reading": {
        "id": 51,
        "device_id": 6,
        "volume": 75,
        "gas": 20,
        "reading_time": "2026-01-06T11:00:00.000Z"
      },
      "created_at": "2025-12-10T10:30:00.000Z"
    }
  ],
  "user_role": "user"
}
```

---

#### API #6: POST /api/devices
**Tujuan**: Buat device (tong) baru

**Header Required**:
```
Authorization: Bearer {token_sanctum}
Content-Type: application/json
```

**Request JSON**:
```json
{
  "nama_device": "Tong Sampah A",
  "lokasi": "Depan Toko",
  "tipe": "smartbin",
  "status": "pending",
  "battery": 100
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Device berhasil dibuat",
  "data": {
    "id": 6,
    "user_id": 1,
    "nama_device": "Tong Sampah A",
    "lokasi": "Depan Toko",
    "tipe": "smartbin",
    "status": "pending",
    "battery": 100,
    "led_status": "off",
    "cleaning_status": "belum",
    "created_at": "2026-01-06T11:00:00.000Z",
    "updated_at": "2026-01-06T11:00:00.000Z"
  }
}
```

**Default Values** (jika tidak dikirim):
- `tipe`: 'smartbin'
- `status`: 'pending'
- `led_status`: 'off'
- `cleaning_status`: 'belum'

**Catatan**: Device baru status='pending', tunggu ACC admin

---

#### API #7: GET /api/devices/{device_id}
**Tujuan**: Dapatkan detail 1 device

**URL**: `GET /api/devices/6`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "data": {
    "id": 6,
    "user_id": 1,
    "nama_device": "Tong Sampah A",
    "lokasi": "Depan Toko",
    "tipe": "smartbin",
    "status": "online",
    "battery": 95,
    "led_status": "on",
    "cleaning_status": "sudah",
    "latest_reading": {
      "id": 51,
      "device_id": 6,
      "volume": 85,
      "gas": 25,
      "reading_time": "2026-01-06T11:00:00.000Z"
    },
    "created_at": "2025-12-10T10:30:00.000Z"
  }
}
```

---

#### API #8: PUT /api/devices/{device_id}
**Tujuan**: Update informasi device

**URL**: `PUT /api/devices/6`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
Content-Type: application/json
```

**Request JSON**:
```json
{
  "nama_device": "Tong A Updated",
  "lokasi": "Belakang Toko",
  "battery": 85,
  "cleaning_status": "belum"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Device berhasil diupdate",
  "data": {
    "id": 6,
    "nama_device": "Tong A Updated",
    "lokasi": "Belakang Toko",
    "battery": 85,
    "cleaning_status": "belum",
    "updated_at": "2026-01-06T11:30:00.000Z"
  }
}
```

---

#### API #9: DELETE /api/devices/{device_id}
**Tujuan**: Hapus device dari database

**URL**: `DELETE /api/devices/6`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Device berhasil dihapus"
}
```

**Catatan**: Juga hapus semua sensor_readings & notifikasi terkait

---

### KATEGORI: SENSOR DATA (4 API)

#### API #10: POST /api/devices/{device_id}/readings
**Tujuan**: Simpan data sensor reading baru

**URL**: `POST /api/devices/6/readings`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
Content-Type: application/json
```

**Request JSON**:
```json
{
  "volume": 75,
  "gas": 20,
  "reading_time": "2026-01-06T11:00:00"
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Sensor reading berhasil disimpan",
  "data": {
    "id": 51,
    "device_id": 6,
    "volume": 75,
    "gas": 20,
    "reading_time": "2026-01-06T11:00:00.000Z",
    "created_at": "2026-01-06T11:00:05.000Z"
  }
}
```

**Validasi**:
- `volume`: integer, 0-100
- `gas`: integer, 0-100 (nullable)
- `device_id`: harus ada di tabel devices

**Trigger**: SensorReadingObserver
- Jika `volume >= 80%` → `devices.led_status = 'on'`
- Jika `gas >= 100` → create notifikasi

---

#### API #11: GET /api/devices/{device_id}/readings
**Tujuan**: Ambil daftar semua readings device (dengan pagination)

**URL**: `GET /api/devices/6/readings?limit=50&page=1`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 51,
        "device_id": 6,
        "volume": 75,
        "gas": 20,
        "reading_time": "2026-01-06T11:00:00.000Z",
        "created_at": "2026-01-06T11:00:05.000Z"
      },
      {
        "id": 50,
        "device_id": 6,
        "volume": 70,
        "gas": 18,
        "reading_time": "2026-01-06T10:55:00.000Z",
        "created_at": "2026-01-06T10:55:05.000Z"
      }
    ],
    "current_page": 1,
    "last_page": 2,
    "per_page": 50,
    "total": 100
  }
}
```

---

#### API #12: GET /api/devices/{device_id}/readings/latest
**Tujuan**: Ambil sensor reading terbaru device

**URL**: `GET /api/devices/6/readings/latest`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "data": {
    "id": 51,
    "device_id": 6,
    "volume": 75,
    "gas": 20,
    "reading_time": "2026-01-06T11:00:00.000Z",
    "created_at": "2026-01-06T11:00:05.000Z"
  }
}
```

---

#### API #13: DELETE /api/devices/{device_id}/readings/{reading_id}
**Tujuan**: Hapus 1 sensor reading data

**URL**: `DELETE /api/devices/6/readings/51`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Reading berhasil dihapus"
}
```

---

### KATEGORI: ESP32 IoT (2 API - PUBLIC)

#### API #14: POST /api/esp32/test
**Tujuan**: Test koneksi ESP32 ke server (debugging)

**URL**: `POST http://192.168.1.5:8000/api/esp32/test`

**Header**: `Content-Type: application/json`

**Autentikasi**: ❌ TIDAK PERLU (Public)

**Request JSON** (any data):
```json
{
  "test": "data"
}
```

**Response Success (200)**:
```json
{
  "status": "success",
  "message": "Koneksi berhasil!",
  "timestamp": "2026-01-06T11:00:00.000Z"
}
```

**Gunakan untuk**: Debug koneksi ESP32 sebelum kirim sensor data

---

#### API #15: POST /api/esp32/sensor
**Tujuan**: Terima data sensor dari ESP32 dan simpan ke DB

**URL**: `POST http://192.168.1.5:8000/api/esp32/sensor`

**Header**: `Content-Type: application/json`

**Autentikasi**: ❌ TIDAK PERLU (Public untuk IoT Device)

**Request JSON**:
```json
{
  "device_id": 6,
  "volume": 75,
  "gas": 20
}
```

**Response Success (200)**:
```json
{
  "status": "success",
  "message": "Data diterima"
}
```

**Flow Lengkap**:
1. ESP32 baca sensor ultrasonic & gas
2. Konversi ke volume (%) & gas (ppm)
3. POST ke endpoint dengan device_id
4. Server validasi & simpan
5. SensorReadingObserver trigger (buzzer/notif)
6. Dashboard auto-update 5 detik kemudian

**Contoh Code Arduino/ESP32**:
```cpp
void sendSensorData() {
  int volume = readUltrasonic(); // 0-100%
  int gas = readGasSensor();     // 0-100 ppm
  
  HTTPClient http;
  http.begin("http://192.168.1.5:8000/api/esp32/sensor");
  http.addHeader("Content-Type", "application/json");
  
  String payload = "{\"device_id\":6,\"volume\":" + String(volume) + 
                   ",\"gas\":" + String(gas) + "}";
  
  int httpCode = http.POST(payload);
  
  if (httpCode == 200) {
    Serial.println("Data sent successfully!");
  }
  http.end();
}
```

---

### KATEGORI: DASHBOARD (3 API)

#### API #16: GET /api/dashboard/data
**Tujuan**: Ambil data real-time untuk dashboard (devices + notifikasi)

**URL**: `GET /api/dashboard/data`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
Cookie: XSRF-TOKEN; laravel_session
```

**Autentikasi**: ✅ Session auth (dari login web)

**Response Success (200)**:
```json
{
  "success": true,
  "devices": [
    {
      "id": 6,
      "user_id": 1,
      "nama_device": "Tong 1",
      "lokasi": "Depan Rumah",
      "tipe": "smartbin",
      "status": "online",
      "battery": 95,
      "led_status": "on",
      "cleaning_status": "belum",
      "latest_reading": {
        "id": 51,
        "device_id": 6,
        "volume": 85,
        "gas": 25,
        "reading_time": "2026-01-06T11:00:00.000Z"
      },
      "created_at": "2025-12-10T10:30:00.000Z"
    }
  ],
  "notifikasis": [
    {
      "id": 1,
      "user_id": 1,
      "device_id": 6,
      "keterangan": "Tong sampah sudah penuh",
      "status": "penuh",
      "is_read": false,
      "user": {
        "id": 1,
        "name": "John Doe"
      },
      "device": {
        "id": 6,
        "nama_device": "Tong 1"
      },
      "created_at": "2026-01-06T11:00:00.000Z"
    }
  ]
}
```

**Update Interval**: JavaScript fetch setiap 5 detik

**Filter**: Hanya notifikasi dengan `is_read = false`

---

#### API #17: GET /api/my-devices
**Tujuan**: Ambil daftar devices user untuk populate tabel

**URL**: `GET /api/my-devices`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
Cookie: XSRF-TOKEN; laravel_session
```

**Autentikasi**: ✅ Session auth

**Response Success (200)**:
```json
[
  {
    "id": 6,
    "user_id": 1,
    "nama_device": "Tong 1",
    "lokasi": "Depan Rumah",
    "tipe": "smartbin",
    "status": "online",
    "battery": 95,
    "led_status": "on",
    "cleaning_status": "belum",
    "latest_reading": {
      "id": 51,
      "device_id": 6,
      "volume": 85,
      "gas": 25,
      "reading_time": "2026-01-06T11:00:00.000Z"
    },
    "created_at": "2025-12-10T10:30:00.000Z"
  },
  {
    "id": 7,
    "user_id": 1,
    "nama_device": "Tong 2",
    "lokasi": "Belakang Rumah",
    "tipe": "smartbin",
    "status": "offline",
    "battery": 45,
    "led_status": "off",
    "cleaning_status": "sudah",
    "latest_reading": {
      "id": 50,
      "device_id": 7,
      "volume": 45,
      "gas": 15,
      "reading_time": "2026-01-06T10:55:00.000Z"
    },
    "created_at": "2025-12-10T10:30:00.000Z"
  }
]
```

**Digunakan untuk**: Populate tabel & chart selection

---

#### API #18: GET /api/sensor-readings
**Tujuan**: Ambil sensor readings untuk grafik (format simpel)

**URL**: `GET /api/sensor-readings?device_id=6`

**Header Required**:
```
Authorization: Bearer {token_sanctum}
Cookie: XSRF-TOKEN; laravel_session
```

**Query Parameters**:
- `device_id` (required): ID device yang dipilih

**Response Success (200)**:
```json
[
  {
    "timestamp": "2026-01-06T10:50:00.000Z",
    "value": 50
  },
  {
    "timestamp": "2026-01-06T10:55:00.000Z",
    "value": 60
  },
  {
    "timestamp": "2026-01-06T11:00:00.000Z",
    "value": 75
  },
  {
    "timestamp": "2026-01-06T11:05:00.000Z",
    "value": 85
  }
]
```

**Format Khusus**: 
- `timestamp` untuk X-axis
- `value` untuk Y-axis (volume)
- Digunakan langsung di Chart.js

**Digunakan untuk**: Line chart grafik real-time

---

## RINGKASAN PRESENTASI

### ✅ Komponen Utama:
1. **Hardware**: 2 Sensor + 2 Actuator (ESP32)
2. **Backend**: Laravel REST API (18 endpoints)
3. **Frontend**: Real-time Dashboard (auto-refresh 5s)
4. **Database**: MySQL (5 tabel + relasi)
5. **IoT Protocol**: HTTP POST (bukan MQTT)
6. **Otomasi**: Observer Pattern (buzzer/notifikasi)

### 📊 API Distribution:
- **Auth**: 4 API (User management)
- **Device CRUD**: 5 API (Tong management)
- **Sensor Data**: 4 API (Reading management)
- **ESP32 IoT**: 2 API (Data receive, test)
- **Dashboard**: 3 API (Real-time display)

### 🎯 Key Features:
✅ Real-time monitoring (volume, gas)
✅ Automatic buzzer control (volume ≥80%)
✅ Auto-generated notifications
✅ Live dashboard updates
✅ Role-based access (user, tukang, admin)
✅ Device approval workflow

---

## LAMPIRAN: CONTOH IMPLEMENTASI

### Folder Struktur Project:
```
tmpt_smph_pintar/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── DeviceController.php
│   │   │       └── SensorReadingController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Device.php
│   │   ├── SensorReading.php
│   │   └── Notifikasi.php
│   └── Observers/
│       └── SensorReadingObserver.php
├── database/
│   └── migrations/
│       ├── 0001_01_01_000000_create_users_table.php
│       ├── 2025_12_10_070940_create_devices_table.php
│       ├── 2025_12_10_070950_create_sensor_readings_table.php
│       └── [+ notifikasi migration]
├── routes/
│   ├── api.php (18 API endpoints)
│   ├── web.php (Web routes)
│   └── console.php
└── resources/
    └── views/
        ├── dashboard_user.blade.php
        ├── dashboard_admin.blade.php
        └── [+ views lainnya]
```

---

**Selesai! File presentasi siap digunakan.** 📑✅
