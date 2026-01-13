# DESAIN SISTEM: Smart Trash Bin (Hardware → Software)

## Ringkasan
Dokumen ini menerjemahkan `code_program.ino` ke rancangan hardware dan software: komponen, pinout, alur data, protokol komunikasi, dan rekomendasi perbaikan.

## Asumsi
- Board: ESP32-C3 (kode menggunakan ESP32Servo, WiFi, HTTPClient).
- Dua sensor ultrasonik untuk deteksi tangan dan pengukuran volume (HC-SR04 atau alternatif).
- Servo SG90 sebagai aktuator penutup.
- Buzzer sebagai indikator.
- (Opsional) Sensor gas analog (MQ-series) untuk deteksi gas/ketidaknyamanan.
- Koneksi WiFi dan API HTTP untuk mengirim data.

## Bill of Materials (BOM) — minimal
- 1x ESP32-C3 (atau ESP32 lain jika kompatibel)
- 2x Ultrasonic sensor (HC-SR04) atau sensor jarak yang sesuai
- 1x Servo mikro (SG90)
- 1x Buzzer (aktif/passif sesuai kebutuhan)
- 5V regulator / power supply (untuk servo & sensor)
- Kabel jumper, breadboard/PCB, konektor
- (Opsional) MQ-135 atau sensor gas lain + pembagi tegangan/ADC

## Pinout (sesuai `code_program.ino`)
- `TRIG_HAND`  -> digital 9  (ultrasonic tangan)
- `ECHO_HAND`  -> digital 8
- `TRIG_VOLUME`-> digital 0  (ultrasonic volume) — PERINGATAN: pin 0/1 pada ESP32 bisa digunakan untuk UART/boot; pertimbangkan pin lain
- `ECHO_VOLUME`-> digital 1  (sama catatan di atas)
- `SERVO_PIN`  -> digital 4
- `BUZZER_PIN` -> digital 2

Rekomendasi: gunakan pin GPIO yang tidak terikat ke fungsi boot/serial (mis. 16,17,18 tergantung board). Pastikan ECHO pins mendukung pulseIn pada ESP32.

## Skematik & Pertimbangan Elektrik
- Sambungkan servo ke 5V power supply terpisah jika servo menarik arus besar; sambungkan GND bersama.
- Buzzer di-pin digital dengan resistor jika diperlukan; gunakan transistor jika perlu arus lebih.
- Ultrasonic TRIG ke output digital, ECHO ke input; beri power 5V (HC-SR04) dan gunakan level shifting jika board 3.3V.
- Gunakan decoupling caps di suplai servo dan sensor.

## Arsitektur Firmware (high level)
1. Inisialisasi: Serial, pinMode, WiFi, attach servo.
2. Loop periodik:
   - Baca sensor tangan (ultrasonic 1).
   - Baca sensor volume (ultrasonic 2) → hitung `lastVolume` (%).
   - Baca sensor gas (jika ada) atau placeholder.
   - Logic: jika `lastVolume >= 80%` → kunci servo & aktifkan buzzer terus.
   - Jika tangan terdeteksi dalam `OPEN_DISTANCE` dan tidak terkunci → buka servo sementara lalu tutup.
   - Kirim data ke API setiap `sendInterval` (HTTP POST JSON).
3. Handling WiFi: reconnect sederhana; pertimbangkan kembali strategi reconnect dan fallback.

## Format Data / API
- Endpoint: `http://<serverIP>:8000/api/esp32/sensor`
- JSON yang dikirim saat ini:
  {"device_id":6,"volume":<int>,"gas":<int>}
- Rekomendasi: tambahkan `timestamp`, `wifi_signal`, `ip`, dan `auth_token`.
- Gunakan HTTPS atau MQTT + TLS bila memungkinkan.

## Alur Keadaan (State Machine)
- IDLE: baca sensor secara berkala.
- HAND_DETECTED: buka servo (timer), kembali ke IDLE.
- FULL_LOCKED: volume >=80% → set LOCKED, buzzer ON, tolak buka.
- SENDING: saat interval kirim, POST ke server; tangani retry/backoff.

## Keamanan & Keandalan
- Gunakan token otentikasi pada API atau MQTT auth.
- Tangani kegagalan HTTP dengan retry terbatas dan log/error state.
- Hindari penggunaan pin yang mengganggu boot/serial.
- Tambahkan filter/smoothing pada pembacaan ultrasonic (median atau eksponensial).

## Perbaikan yang Disarankan
- Ganti HTTP polling dengan MQTT untuk efisiensi dan realtime.
- Tambah OTA update (ArduinoOTA atau ESP32HTTPUpdate) untuk maintenance.
- Implementasi retry/backoff dan status LED untuk debugging lapangan.
- Kalibrasi sensor ultrasonic untuk dimensi tong (offset jarak pengukuran).
- Implementasi pembacaan gas analog nyata, threshold configurable.
- Power-saving: deep sleep jika perangkat berbasis baterai dan bukan selalu aktif.

## Diagram (ringkas)

ESP32-C3
  ├─ Ultrasonic #1 (TRIG_HAND / ECHO_HAND) — deteksi tangan
  ├─ Ultrasonic #2 (TRIG_VOLUME / ECHO_VOLUME) — ukur kedalaman/volume
  ├─ Servo (SERVO_PIN) — aktuator penutup
  ├─ Buzzer (BUZZER_PIN)
  └─ WiFi → API Server (HTTP POST JSON)

## Langkah Selanjutnya
1. Uji kode pada breadboard sesuai pinout; ganti pin 0/1 jika bermasalah.
2. Buat skematik dan PCB (jika perlu).
3. Implementasikan rekomendasi: secure comms, OTA, smoothing sensor.
4. Tambah diagram blok dan sequence (file terpisah di `docs/diagrams/`).

---
Dokumen ini dibuat berdasarkan `code_program.ino`. Bila Anda mau, saya bisa: membuat skematik Eagle/KiCad sederhana, gambar diagram blok merinci, atau memperbarui kode (mis. pindah pin, tambahkan MQTT/OTA, atau pembacaan gas nyata).