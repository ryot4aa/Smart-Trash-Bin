<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'device_id',
        'volume',
        'gas',
        'reading_time'
    ];

    protected $casts = [
        'volume' => 'integer',
        'gas' => 'integer',
        'reading_time' => 'datetime'
    ];

    /**
     * Get the device this reading belongs to
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
