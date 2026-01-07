<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class TukangController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        // Ambil notifikasi untuk semua device (atau bisa difilter sesuai kebutuhan tukang)
        $notifikasis = \App\Models\Notifikasi::with(['user', 'device'])
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();
        $devices = \App\Models\Device::with('latestReading')->get();
        $users = \App\Models\User::with(['devices.latestReading'])->get();
        return view('dashboard_tukang', compact('devices', 'users', 'notifikasis'));
    }

    public function updateCleaningStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:sudah,belum',
        ]);
        $device = Device::findOrFail($id);
        $device->cleaning_status = $request->status;
        $device->save();
        return redirect()->back()->with('success', 'Status cleaning berhasil diupdate!');
    }
}
