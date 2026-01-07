<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Notifikasi;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $devices = Device::with('latestReading')->where('user_id', $user->id)->get();
        $notifikasis = Notifikasi::with(['user', 'device'])
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();
        return view('dashboard_user', compact('devices', 'notifikasis'));
    }
}
