<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'nama_device',
        'lokasi',
        'tipe',
        'status',
        'battery',
        'led_status',
        'cleaning_status',
    ];

    protected $casts = [
        'battery' => 'integer',
        'led_status' => 'string',
        'cleaning_status' => 'string',
    ];

    /**
     * Get the user that owns the device
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sensor readings for this device
     */
    public function sensorReadings()
    {
        return $this->hasMany(SensorReading::class);
    }

    /**
     * Get the latest sensor reading
     */
    public function latestReading()
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('reading_time');
    }
}
