<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorReading;
use App\Models\Notifikasi;

class GenerateNotifikasiFromSensorReadings extends Command
{
    protected $signature = 'notifikasi:generate-from-sensor';
    protected $description = 'Generate notifikasi dari data sensor_readings lama';

    public function handle()
    {
        $volumeThreshold = 80;
        $gasThreshold = 100;
        $count = 0;

        $readings = SensorReading::with('device.user')->get();
        foreach ($readings as $reading) {
            $device = $reading->device;
            $user = $device ? $device->user : null;
            if (!$device || !$user) continue;

            // Notifikasi penuh
            if ($reading->volume >= $volumeThreshold) {
                $notifExists = Notifikasi::where([
                    ['device_id', '=', $device->id],
                    ['status', '=', 'penuh'],
                    ['is_read', '=', false],
                ])->exists();
                if (!$notifExists) {
                    Notifikasi::create([
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'keterangan' => 'Tong sampah sudah penuh',
                        'status' => 'penuh',
                        'is_read' => false,
                    ]);
                    $count++;
                }
            }
            // Notifikasi gas
            if ($reading->gas >= $gasThreshold) {
                $notifExists = Notifikasi::where([
                    ['device_id', '=', $device->id],
                    ['status', '=', 'gas_berbahaya'],
                    ['is_read', '=', false],
                ])->exists();
                if (!$notifExists) {
                    Notifikasi::create([
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'keterangan' => 'Gas melebihi batas aman',
                        'status' => 'gas_berbahaya',
                        'is_read' => false,
                    ]);
                    $count++;
                }
            }
        }
        $this->info("Notifikasi yang dibuat: $count");
    }
}
