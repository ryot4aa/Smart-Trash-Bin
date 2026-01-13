<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Models\Device;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login.form');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/admin', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard.admin');
    Route::get('/dashboard/user', [App\Http\Controllers\UserController::class, 'dashboard'])->name('dashboard.user');

    Route::get('/dashboard/tukang', [App\Http\Controllers\TukangController::class, 'dashboard'])->name('dashboard.tukang');

    // Pendaftaran tong via web form (session auth)
    Route::post('/devices/register', [DeviceController::class, 'store'])->name('devices.register');

    // Route untuk update cleaning status oleh tukang
    Route::post('/tukang/device/{id}/cleaning', [App\Http\Controllers\TukangController::class, 'updateCleaningStatus'])->name('tukang.cleaning.update');

    // Dashboard admin

    Route::post('/admin/register-user', [App\Http\Controllers\AdminController::class, 'registerUser'])->name('admin.registerUser');
    // Pendaftaran tukang oleh admin
    Route::post('/admin/register-tukang', [App\Http\Controllers\AdminController::class, 'registerTukang'])->name('admin.registerTukang');

    // ACC tong oleh admin
    Route::post('/admin/acc-tong/{device}', [App\Http\Controllers\AdminController::class, 'accTong'])->name('admin.accTong');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    // Upload profile photo (admin/user)
    Route::post('/profile/upload-photo', [App\Http\Controllers\Api\AuthController::class, 'uploadPhoto'])->name('profile.uploadPhoto');

    // Temporary debug route: create a test device for the currently authenticated user
    Route::get('/dev/create-my-device', function() {
        if (app()->environment('production')) {
            abort(404);
        }
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login.form');
        }
        $device = Device::create([
            'user_id' => $user->id,
            'nama_device' => 'AutoDevice ' . time(),
            'lokasi' => 'Auto-created',
            'tipe' => 'smartbin',
            'status' => 'online',
            'battery' => 100,
            'buzzer_status' => 'off',
            'cleaning_status' => 'belum',
        ]);
        return redirect()->back()->with('success', 'Device created ID: ' . $device->id);
    })->name('dev.createMyDevice');
});
