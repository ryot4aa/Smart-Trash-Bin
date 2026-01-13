#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>

// ===== PIN untuk ESP32-C3 Expansion Board =====
#define TRIG_HAND   9    // Ultrasonic 1 - Deteksi tangan
#define ECHO_HAND   8
#define TRIG_VOLUME 0    // Ultrasonic 2 - Volume sampah (hasil scan)
#define ECHO_VOLUME 1
#define SERVO_PIN   4
#define BUZZER_PIN  2

// ===== PARAMETER =====
#define OPEN_DISTANCE 15
#define OPEN_ANGLE    -2
#define CLOSE_ANGLE   70
#define EMPTY_DISTANCE 50   // cm saat tong kosong
#define FULL_DISTANCE  5    // cm saat tong penuh

// ===== WiFi STA (Menerima WiFi) =====
const char* ssid = "HOTSPOT-ITENAS";
const char* password = "";

// ===== API Configuration =====
const char* serverIP = ""; //IP LEPTOP
const int serverPort = 8000;
const char* apiEndpoint = "/api/esp32/sensor";
const unsigned long sendInterval = 5000;   // Kirim setiap 5 detik

Servo myServo;

// Data monitoring
long lastDistance = 999;       // tangan
long lastVolDistance = 999;    // volume
int lastVolume = 0;            // % penuh
int lastGas = 0;               // placeholder gas (masih dummy)
bool servoOpen = false;
unsigned long lastSendTime = 0;
bool volumeLocked = false;     // kunci jika volume >=80%
unsigned long lastBuzzTime = 0;

long readUltrasonic() {
  digitalWrite(TRIG_HAND, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_HAND, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_HAND, LOW);

  long duration = pulseIn(ECHO_HAND, HIGH, 30000);
  if (duration == 0) return 999;
  return duration * 0.034 / 2;
}

void openServo() {
  Serial.println("Servo buka");
  digitalWrite(BUZZER_PIN, HIGH);
  delay(100);
  digitalWrite(BUZZER_PIN, LOW);
  myServo.write(OPEN_ANGLE);
  servoOpen = true;
}

void closeServo() {
  Serial.println("Servo tutup");
  myServo.write(CLOSE_ANGLE);
  servoOpen = false;
}

long readVolumeUltrasonic() {
  digitalWrite(TRIG_VOLUME, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_VOLUME, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_VOLUME, LOW);

  long duration = pulseIn(ECHO_VOLUME, HIGH, 30000);
  if (duration == 0) return 999;
  return duration * 0.034 / 2;
}

int calculateVolume(long distance) {
  if (distance == 0 || distance >= 999) return 0;
  int vol = map(constrain(distance, FULL_DISTANCE, EMPTY_DISTANCE),
                EMPTY_DISTANCE, FULL_DISTANCE, 0, 100);
  return constrain(vol, 0, 100);
}

void sendDataToAPI() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ WiFi tidak terhubung");
    return;
  }

  HTTPClient http;
  String url = String("http://") + serverIP + ":" + serverPort + apiEndpoint;
  
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  
  // Buat JSON sesuai format API Anda
  String jsonData = "{";
  jsonData += "\"device_id\":6,";
  jsonData += "\"volume\":" + String(lastVolume) + ",";
  jsonData += "\"gas\":" + String(lastGas);
  jsonData += "}";
  
  Serial.println("📤 Mengirim: " + jsonData);
  
  int httpCode = http.POST(jsonData);
  
  if (httpCode > 0) {
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_CREATED) {
      String response = http.getString();
      Serial.println("✓ Data terkirim");
    } else {
      Serial.println("⚠️ HTTP Error: " + String(httpCode));
    }
  } else {
    Serial.println("⚠️ Gagal kirim: " + http.errorToString(httpCode));
  }
  
  http.end();
}

void setup() {
  Serial.begin(115200);
  delay(2000);

  Serial.println("\n\n=== ESP32-C3 TONG SAMPAH OTOMATIS ===");
  Serial.println("Inisialisasi...");

  // Setup ultrasonic pins
  pinMode(TRIG_HAND, OUTPUT);
  pinMode(ECHO_HAND, INPUT);
  digitalWrite(TRIG_HAND, LOW);
  Serial.println("✓ Ultrasonic OK");

  // Setup ultrasonic 2 (volume)
  pinMode(TRIG_VOLUME, OUTPUT);
  pinMode(ECHO_VOLUME, INPUT);
  digitalWrite(TRIG_VOLUME, LOW);
  Serial.println("✓ Ultrasonic 2 (Volume) OK");

  // Setup buzzer
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  Serial.println("✓ Buzzer OK");

  // Setup WiFi DULU sebelum Servo
  WiFi.mode(WIFI_STA);
  WiFi.disconnect(false);
  delay(500);
  WiFi.begin(ssid, password);
  Serial.println("Menghubungkan ke WiFi...");
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✓ WiFi Connected");
    Serial.print("SSID: "); Serial.println(WiFi.SSID());
    Serial.print("IP: "); Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n⚠️ WiFi Failed");
  }

  // Setup servo SETELAH WiFi
  myServo.attach(SERVO_PIN);
  myServo.write(CLOSE_ANGLE);
  Serial.println("✓ Servo OK");

  Serial.println("\n=== SISTEM SIAP ===");
  Serial.println("✓ Data dikirim ke API setiap 5 detik");
  Serial.println("✓ Servo akan terkunci & buzzer ON jika volume >=80%");
}

void loop() {
  // Baca sensor tangan
  lastDistance = readUltrasonic();

  // Baca sensor volume
  lastVolDistance = readVolumeUltrasonic();
  lastVolume = calculateVolume(lastVolDistance);
  // Gas masih dummy: pakai nilai acak ringan dulu agar terlihat berubah
  lastGas = random(0, 50);

  // Lock & buzzer jika volume >=80%
  if (lastVolume >= 80) {
    volumeLocked = true;
    digitalWrite(BUZZER_PIN, HIGH); // buzzer menyala terus
    if (servoOpen) {
      closeServo(); // pastikan terkunci
    }
  } else {
    volumeLocked = false;
    digitalWrite(BUZZER_PIN, LOW);
  }

  // Bunyikan buzzer jika volume >=80% (cooldown 10 detik)
  if (lastVolume >= 80 && millis() - lastBuzzTime > 10000) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(150);
    digitalWrite(BUZZER_PIN, LOW);
    delay(150);
    digitalWrite(BUZZER_PIN, HIGH);
    delay(150);
    digitalWrite(BUZZER_PIN, LOW);
    lastBuzzTime = millis();
  }

  Serial.print("Jarak: ");
  Serial.print(lastDistance);
  Serial.print("cm | Volume: ");
  Serial.print(lastVolume);
  Serial.print("% (");
  Serial.print(lastVolDistance);
  Serial.print("cm) | Gas: ");
  Serial.print(lastGas);
  Serial.print(" | Servo: ");
  Serial.println(servoOpen ? "BUKA" : "TUTUP");

  // Auto buka jika tangan terdeteksi
  if (!volumeLocked && lastDistance > 0 && lastDistance <= OPEN_DISTANCE) {
    if (!servoOpen) {
      openServo();
      delay(3000);
      closeServo();
      delay(1000);
    }
  }

  // Kirim data ke API setiap 5 detik
  unsigned long currentTime = millis();
  if (currentTime - lastSendTime >= sendInterval) {
    sendDataToAPI();
    lastSendTime = currentTime;
  }

  delay(300);
}
