<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUser = User::where('role', 'user')->count();
        $totalTukang = User::where('role', 'tukang')->count();
        $users = User::with(['devices.latestReading'])->where('role', 'user')->get();
        $tukangs = User::where('role', 'tukang')->get();
        $notifikasis = \App\Models\Notifikasi::with(['user', 'device'])->where('is_read', false)->latest()->take(10)->get();
        return view('dashboard_admin', compact('totalUser', 'totalTukang', 'users', 'tukangs', 'notifikasis'));
    }

    public function registerUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'alamat' => 'nullable|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,tukang,admin',
        ]);
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'alamat' => $validated['alamat'] ?? null,
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);
        return back()->with('success', 'User berhasil didaftarkan!');
    }

    public function registerTukang(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'alamat' => 'nullable|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,tukang,admin',
        ]);
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'alamat' => $validated['alamat'] ?? null,
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);
        return back()->with('success', 'Tukang berhasil didaftarkan!');
    }

    public function accTong(Request $request, \App\Models\Device $device)
    {
        $action = $request->input('action');
        
        if ($action === 'acc') {
            $device->update(['status' => 'online']);
            return back()->with('success', 'Tong berhasil di-ACC!');
        } elseif ($action === 'tolak') {
            $device->delete();
            return back()->with('success', 'Tong berhasil ditolak!');
        }
        
        return back()->with('error', 'Aksi tidak valid!');
    }
}
