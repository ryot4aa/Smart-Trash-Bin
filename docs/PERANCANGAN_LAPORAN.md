# PERANCANGAN SISTEM (Versi Laporan)

Proyek: Smart Trash Bin — ESP32-C3
Tanggal: 2026-01-08

## 1. Tujuan
Dokumen ini hanya memaparkan perancangan perangkat keras dan perangkat lunak secara ringkas untuk keperluan laporan tugas/praktikum.

## 2. Ruang Lingkup
- Perancangan hardware: komponen utama, koneksi dan pinout.
- Perancangan software: arsitektur firmware, alur kontrol, dan format komunikasi.

## 3. Asumsi
- Board: ESP32-C3 (3.3V I/O).
- Sensor jarak: 2× HC-SR04 (atau sensor jarak sejenis).
- Aktuator: 1× servo mikro (SG90).
- Indikator: 1× buzzer.
- Konektivitas: WiFi, kirim data ke API HTTP.

## 4. Komponen Utama (BOM singkat)
- ESP32-C3 (1)
- HC-SR04 (2)
- Servo SG90 (1)
- Buzzer (1)
- Regulator 5V / catu daya (untuk servo)
- Kabel, breadboard atau PCB

## 5. Pinout (rekomendasi untuk laporan)
Catatan: Hindari GPIO yang digunakan untuk boot/serial (mis. GPIO0/1) jika memungkinkan.
- TRIG_HAND: GPIO 9 (output)
- ECHO_HAND: GPIO 8 (input)
- TRIG_VOLUME: GPIO 16 (output) — rekomendasi pindah dari 0
- ECHO_VOLUME: GPIO 17 (input)  — rekomendasi pindah dari 1
- SERVO_PIN: GPIO 4 (PWM)
- BUZZER_PIN: GPIO 2 (output)

## 6. Skematik Singkat (deskripsi)
- Semua GND disatukan.
- HC-SR04 Vcc ke 5V, TRIG ke output ESP32 (3.3V), ECHO melalui level-shifter ke 3.3V input ESP32.
- Servo Vcc ke 5V terpisah bila perlu, GND bersama.
- Buzzer ke GPIO melalui resistor atau transistor jika perlu arus lebih.

## 7. Arsitektur Software (ringkas)
- Inisialisasi: Serial, pinMode, WiFi (STA), attach servo.
- Loop utama:
  1. Baca ultrasonic tangan → untuk deteksi buka.
  2. Baca ultrasonic volume → hitung persentase penuh (`calculateVolume`).
  3. Jika volume >= 80% → set `LOCKED`, aktifkan buzzer dan tolak buka.
  4. Jika tangan terdeteksi dan tidak `LOCKED` → buka servo selama t_open (mis. 3s), lalu tutup.
  5. Kirim data telemetry (JSON) ke server setiap interval (mis. 5s).

## 8. State Machine
- IDLE
- HAND_DETECTED → OPEN → TIMED_CLOSE → IDLE
- FULL_LOCKED (buzzer ON, buka ditolak)
- SENDING (kegiatan kirim data)

## 9. Format Data (untuk laporan)
Contoh payload JSON:
{
  "device_id": 6,
  "volume": 42,
  "gas": 12,
  "timestamp": "2026-01-08T10:00:00Z"
}

## 10. Pengujian dan Verifikasi (singkat)
- Verifikasi pembacaan jarak pada beberapa titik (5, 10, 20, 30, 50 cm).
- Uji buka otomatis dengan tangan pada jarak < treshold.
- Uji kondisi penuh dengan menempatkan objek dekat sensor volume.
- Uji pengiriman HTTP ke endpoint uji.

## 11. Rekomendasi Singkat
- Gunakan pin yang tidak mengganggu boot.
- Terapkan filter (median) untuk pembacaan ultrasonic.
- Hindari `delay()` blocking; gunakan `millis()` untuk penjadwalan.
- Pertimbangkan MQTT/TLS untuk komunikasi aman dan efisien.

---
Dokumen ini difokuskan pada bagian perancangan untuk disertakan langsung ke laporan. Jika perlu, saya bisa menyesuaikan gaya (lebih akademik) atau menambahkan gambar skematik/breadboard untuk lampiran.