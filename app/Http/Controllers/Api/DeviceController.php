<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of all devices
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Admin dan Tukang dapat melihat semua device, User hanya device miliknya
        $query = ($user->role === 'admin' || $user->role === 'tukang') ? Device::query() : Device::where('user_id', $user->id);
        
        $devices = $query->with('latestReading')->get();
        
        return response()->json([
            'success' => true,
            'data' => $devices,
            'user_role' => $user->role,
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
    }

    /**
     * Store a newly created device
     */
    public function store(Request $request)
    {
        // Support Sanctum (API) dan session auth (web form)
        $user = $request->user('sanctum') ?? $request->user();
        
        if (!$user) {
            // Untuk web form: redirect dengan pesan error
            if (!$request->wantsJson()) {
                return redirect()->back()->with('error', 'Silakan login terlebih dahulu');
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validated = $request->validate([
            'nama_device' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'tipe' => 'nullable|string|max:255',
            'status' => 'nullable|in:online,offline,pending',
            'battery' => 'nullable|integer|min:0|max:100'
        ]);

        $validated['user_id'] = $user->id;
        // Set default values for optional fields when form tidak mengirimkannya
        $validated['tipe'] = $validated['tipe'] ?? 'smartbin';
        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['buzzer_status'] = $validated['buzzer_status'] ?? 'off';
        $validated['cleaning_status'] = $validated['cleaning_status'] ?? 'belum';

        $device = Device::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Device berhasil dibuat',
                'data' => $device
            ], 201);
        }

        return redirect()->back()->with('success', 'Tong berhasil didaftarkan. ID: ' . $device->id);
    }

    /**
     * Display the specified device
     */
    public function show(Request $request, Device $device)
    {
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check authorization: User hanya bisa lihat device miliknya, Admin bisa lihat semua
        if ($user->role !== 'admin' && $device->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $device->load('latestReading');

        return response()->json([
            'success' => true,
            'data' => $device
        ]);
    }

    /**
     * Update the specified device
     */
    public function update(Request $request, Device $device)
    {
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check authorization
        if ($user->role !== 'admin' && $device->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'nama_device' => 'string|max:255',
            'lokasi' => 'string|max:255',
            'tipe' => 'string|max:255',
            'status' => 'in:online,offline',
            'battery' => 'integer|min:0|max:100'
        ]);

        $device->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil diupdate',
            'data' => $device
        ]);
    }

    /**
     * Remove the specified device
     */
    public function destroy(Request $request, Device $device)
    {
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check authorization
        if ($user->role !== 'admin' && $device->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil dihapus'
        ]);
    }
    /**
     * Control actuator (Buzzer) pada device
     */
    public function control(Request $request, Device $device)
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        // Hanya admin atau owner device
        if ($user->role !== 'admin' && $device->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $validated = $request->validate([
            'buzzer' => 'required|in:on,off'
        ]);
        $device->buzzer_status = $validated['buzzer'];
        $device->save();
        return response()->json([
            'success' => true,
            'message' => 'Buzzer status updated',
            'data' => [
                'buzzer_status' => $device->buzzer_status
            ]
        ]);
    }
}
