<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    // Use Laravel conventions: table `users`, primary key `id` and timestamps
    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'kontak',
        'role',
        'alamat',
        'nomor_telepon',
        'profile_photo',
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Get all devices owned by this user
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
