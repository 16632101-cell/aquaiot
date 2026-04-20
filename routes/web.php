<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IoTController;
use App\Models\IoTDevice;

Route::get('/', function () { return redirect('/login'); });

// ระบบ Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ระบบ Dashboard (ต้อง Login ก่อน)
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', function () {
        $devices = IoTDevice::all(); 
        return view('dashboard', compact('devices'));
    })->name('dashboard');

    // จัดการอุปกรณ์ (เฉพาะ Admin)
    Route::post('/add-device', [IoTController::class, 'addDevice']);
    Route::post('/delete-device/{device_id}', [IoTController::class, 'deleteDevice']);
    Route::post('/toggle-device/{device_id}', [IoTController::class, 'toggleDeviceStatus']);
    
    // เส้นทางสำหรับเซฟเกณฑ์แจ้งเตือน และ ส่งคำสั่ง (AJAX)
    Route::post('/update-thresholds/{device_id}', [IoTController::class, 'updateThresholds']);
    Route::post('/send-command', [IoTController::class, 'sendCommand']);
});