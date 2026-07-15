<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IoTController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// Routes สำหรับ Arduino (ไม่ต้อง Login)
// ==========================================

// 1. Arduino ส่งค่าเซ็นเซอร์มาเก็บ
Route::post('/sensor-data', [IoTController::class, 'storeData']);

// 2. Arduino ดึงคำสั่งล่าสุด (ใช้ชื่อ /get-command/ เพื่อให้ตรงกับโค้ดที่เรียกใน Arduino)
Route::get('/get-command/{device_id}', [IoTController::class, 'getCommand']);

// ==========================================
// Routes สำหรับ Dashboard (ต้อง Login)
// ==========================================

// 3. Dashboard ดึงข้อมูลล่าสุดแบบ Real-time
Route::get('/get-latest-data/{device_id}', [IoTController::class, 'getLatestData']);

// 4. สั่งงานผ่าน Dashboard (ป้องกันด้วย auth:sanctum)
Route::middleware('auth:sanctum')->post('/send-command', [IoTController::class, 'sendCommand']);