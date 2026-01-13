<?php
namespace App\Observers;

use App\Models\SensorReading;
use App\Models\Notifikasi;
use App\Models\Device;

class SensorReadingObserver
{
    public function created(SensorReading $reading)
    {
        $this->handleNotif($reading);
    }

    public function updated(SensorReading $reading)
    {
        $this->handleNotif($reading);
    }

    protected function handleNotif(SensorReading $reading)
    {
        \Log::info('SensorReadingObserver: handleNotif dipanggil', [
            'sensorReading_id' => $reading->id,
            'device_id' => $reading->device_id,
            'volume' => $reading->volume,
            'gas' => $reading->gas,
        ]);
        $device = $reading->device;
        if (!$device) {
            \Log::warning('SensorReadingObserver: device null', [
                'sensorReading_id' => $reading->id,
                'device_id' => $reading->device_id,
            ]);
            return;
        }
        $user = $device->user;
        if (!$user) {
            \Log::warning('SensorReadingObserver: user null', [
                'device_id' => $device->id,
            ]);
            return;
        }
        $volumeThreshold = 80; // misal 80% penuh
        $gasThreshold = 100;   // misal 100 ppm

        // Update status buzzer
        if ($reading->volume !== null) {
            $device->buzzer_status = $reading->volume >= $volumeThreshold ? 'on' : 'off';
            $device->save();
        }

        // Cek notifikasi penuh
        if ($reading->volume >= $volumeThreshold) {
            $notifExists = Notifikasi::where([
                ['device_id', '=', $device->id],
                ['status', '=', 'penuh'],
                ['is_read', '=', false],
            ])->exists();
            if (!$notifExists) {
                \Log::info('SensorReadingObserver: Buat notifikasi penuh', [
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                ]);
                Notifikasi::create([
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                    'keterangan' => 'Tong sampah sudah penuh',
                    'status' => 'penuh',
                    'is_read' => false,
                ]);
            }
        } else {
            // Volume turun di bawah threshold, mark notifikasi sebagai read
            Notifikasi::where([
                ['device_id', '=', $device->id],
                ['status', '=', 'penuh'],
                ['is_read', '=', false],
            ])->update(['is_read' => true]);
        }

        // Cek notifikasi gas
        if ($reading->gas >= $gasThreshold) {
            $notifExists = Notifikasi::where([
                ['device_id', '=', $device->id],
                ['status', '=', 'gas_berbahaya'],
                ['is_read', '=', false],
            ])->exists();
            if (!$notifExists) {
                \Log::info('SensorReadingObserver: Buat notifikasi gas', [
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                ]);
                Notifikasi::create([
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                    'keterangan' => 'Gas melebihi batas aman',
                    'status' => 'gas_berbahaya',
                    'is_read' => false,
                ]);
            }
        } else {
            // Gas turun di bawah threshold, mark notifikasi sebagai read
            Notifikasi::where([
                ['device_id', '=', $device->id],
                ['status', '=', 'gas_berbahaya'],
                ['is_read', '=', false],
            ])->update(['is_read' => true]);
        }
    }
}
