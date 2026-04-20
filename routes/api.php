<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IoTController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| ทุกๆ Route ในหน้านี้ จะมีคำว่า /api นำหน้าให้โดยอัตโนมัติ
*/

// ==========================================
// Routes สำหรับ Arduino (ไม่ต้อง Login)
// ==========================================

// 1. Arduino ส่งค่าเซ็นเซอร์มาเก็บ
Route::post('/sensor-data', [IoTController::class, 'storeData']);

// 2. Arduino ดึงคำสั่งล่าสุด (เปิด/ปิดรีเลย์)
Route::get('/device-command/{device_id}', [IoTController::class, 'getCommand']);

// ==========================================
// Routes สำหรับ Dashboard (ต้อง Login)
// ==========================================

// 3. Dashboard ดึงข้อมูลล่าสุดแบบ Real-time
Route::get('/get-latest-data/{device_id}', [IoTController::class, 'getLatestData']);

// 4. แก้ไข: ป้องกัน send-command ด้วย auth:sanctum
//    ก่อนหน้านี้ route นี้เปิดโล่ง ใครก็ POST มาสั่งเปิด/ปิดปั๊มได้
//    ตอนนี้ต้องมี session cookie ที่ valid (login แล้ว) จึงจะใช้งานได้
Route::middleware('auth:sanctum')->post('/send-command', [IoTController::class, 'sendCommand']);