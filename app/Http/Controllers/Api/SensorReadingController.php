<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class SensorReadingController extends Controller
{
    /**
     * Get sensor readings for a specific device
     */
    public function index(Request $request, Device $device)
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

        $limit = $request->query('limit', 50);
        $readings = $device->sensorReadings()
            ->orderBy('reading_time', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $readings
        ]);
    }

    /**
     * Store a new sensor reading
     */
    public function store(Request $request, Device $device)
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
            'volume' => 'required|integer|min:0|max:100',
            'gas' => 'nullable|integer|min:0|max:100',
            'reading_time' => 'nullable|date_format:Y-m-d H:i:s'
        ]);

        $validated['device_id'] = $device->id;
        
        // Jika reading_time tidak diberikan, gunakan waktu sekarang
        if (!isset($validated['reading_time'])) {
            $validated['reading_time'] = now();
        }

        $reading = SensorReading::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sensor reading berhasil disimpan',
            'data' => $reading
        ], 201);
    }

    /**
     * Get the latest sensor reading for a device
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

        // Check authorization
        if ($user->role !== 'admin' && $device->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $reading = $device->sensorReadings()
            ->orderBy('reading_time', 'desc')
            ->first();

        if (!$reading) {
            return response()->json([
                'success' => false,
                'message' => 'No sensor readings found for this device'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reading
        ]);
    }

    /**
     * Delete a sensor reading
     */
    public function destroy(Request $request, Device $device, SensorReading $reading)
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

        // Verify reading belongs to device
        if ($reading->device_id !== $device->id) {
            return response()->json([
                'success' => false,
                'message' => 'Reading not found for this device'
            ], 404);
        }

        $reading->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sensor reading berhasil dihapus'
        ]);
    }
}
