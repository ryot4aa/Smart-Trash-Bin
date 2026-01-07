<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\SensorReading;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

// API untuk real-time dashboard data (session auth untuk web)
Route::middleware('web')->get('/dashboard/data', function(Request $request) {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }
    
    $devices = \App\Models\Device::with('latestReading')
        ->where('user_id', $user->id)
        ->get();
    
    $notifikasis = \App\Models\Notifikasi::with(['user', 'device'])
        ->where('user_id', $user->id)
        ->where('is_read', false)
        ->latest()
        ->take(10)
        ->get();
    
    return response()->json([
        'success' => true,
        'devices' => $devices,
        'notifikasis' => $notifikasis
    ]);
});

// Endpoint untuk fetch devices user (untuk tabel)
Route::middleware('web')->get('/my-devices', function(Request $request) {
    $user = auth()->user();
    if (!$user) {
        return response()->json([]);
    }
    
    $devices = \App\Models\Device::with('latestReading')
        ->where('user_id', $user->id)
        ->get();
    
    return response()->json($devices);
});

// ESP32 ENDPOINT - Test koneksi (tanpa authentication)
Route::post('/esp32/test', function(Request $request) {
    Log::info('ESP32 Test Connection:', $request->all());
    
    return response()->json([
        'status' => 'success',
        'message' => 'Koneksi berhasil!',
        'timestamp' => now()
    ]);
});

// ESP32 ENDPOINT - Kirim data sensor (tanpa authentication)
Route::post('/esp32/sensor', function(Request $request) {
    Log::info('Data ESP32:', $request->all());
    
    // Simpan ke database (volume & gas optional untuk testing)
    if ($request->has('device_id')) {
        SensorReading::create([
            'device_id' => $request->device_id,
            'volume' => $request->volume ?? 0,  // Default 0 jika tidak ada
            'gas' => $request->gas ?? 0,        // Default 0 jika tidak ada
            'reading_time' => now()
        ]);
        
        // Update status device jadi online
        Device::where('id', $request->device_id)->update(['status' => 'online']);
    }
    
    return response()->json([
        'status' => 'success',
        'message' => 'Data diterima'
    ]);
});

// Endpoint untuk ambil data sensor berdasarkan device_id (untuk dashboard user)
Route::get('/sensor-readings', function (Request $request) {
    $deviceId = $request->query('device_id');
    if (!$deviceId) {
        return response()->json([]);
    }
    $readings = SensorReading::where('device_id', $deviceId)
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function($item) {
            return [
                'timestamp' => $item->created_at,
                'value' => $item->volume
            ];
        });
    return response()->json($readings);
});
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SensorReadingController;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working'
    ]);
});

Route::get('/debug', function (Request $request) {
    return response()->json([
        'auth_header' => $request->header('Authorization'),
        'user_sanctum' => $request->user('sanctum') ? $request->user('sanctum')->toArray() : null,
        'bearerToken' => $request->bearerToken()
    ]);
});

// AUTH ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/me', [AuthController::class, 'profile']);

// DEVICE ROUTES - Protected with Bearer token
Route::prefix('/devices')->group(function () {
    Route::get('', [DeviceController::class, 'index']);              // GET /api/devices
    Route::post('', [DeviceController::class, 'store']);              // POST /api/devices
    Route::get('/{device}', [DeviceController::class, 'show']);       // GET /api/devices/{id}
    Route::put('/{device}', [DeviceController::class, 'update']);     // PUT /api/devices/{id}
    Route::delete('/{device}', [DeviceController::class, 'destroy']); // DELETE /api/devices/{id}
    Route::post('/{device}/control', [DeviceController::class, 'control']); // POST /api/devices/{device}/control
    
    // SENSOR READING ROUTES - Nested under devices
    Route::get('/{device}/readings', [SensorReadingController::class, 'index']);              // GET /api/devices/{device}/readings
    Route::post('/{device}/readings', [SensorReadingController::class, 'store']);              // POST /api/devices/{device}/readings
    Route::get('/{device}/readings/latest', [SensorReadingController::class, 'show']);         // GET /api/devices/{device}/readings/latest
    Route::delete('/{device}/readings/{reading}', [SensorReadingController::class, 'destroy']); // DELETE /api/devices/{device}/readings/{id}
});
