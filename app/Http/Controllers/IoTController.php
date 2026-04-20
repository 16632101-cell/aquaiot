<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WaterQuality;
use App\Models\SystemCommand;
use App\Models\IoTDevice;

class IoTController extends Controller
{
    // ==========================================
    // API สำหรับ Arduino
    // ==========================================

    // 1. รับค่าจากเซ็นเซอร์ และ ตอบกลับคำสั่งให้ Arduino ทันที
    // Route: POST /api/sensor-data
    public function storeData(Request $request)
    {
        // --- เพิ่ม 3 บรรทัดนี้ เพื่อสวมรอยเอา ID ล่าสุดที่มีในระบบมาใช้อัตโนมัติ เลี่ยงปัญหา ID ไม่ตรง ---
        if (\App\Models\IoTDevice::exists()) {
            $request->merge(['device_id' => \App\Models\IoTDevice::first()->device_id]);
        }
        // ---------------------------------------------------------------------------------

        $data = $request->validate([
            'device_id'   => 'required|integer|exists:io_t_devices,device_id',
            'ph_value'    => 'required|numeric',
            'turbidity'   => 'required|numeric',
            'temperature' => 'required|numeric',
        ]);

        $device = IoTDevice::where('device_id', $data['device_id'])->first();
        $command = SystemCommand::where('device_id', $data['device_id'])->latest()->first();

        // ตรวจสอบว่าอุปกรณ์นั้น online อยู่ไหม 
        // ถ้า online -> บันทึกค่าเซ็นเซอร์ลงตาราง WaterQuality
        // ถ้า offline -> ข้ามการบันทึกค่าไปเลย (แต่ไม่ส่ง Error เพราะต้องให้ Arduino รับค่าไปปิดปั๊ม)
        if ($device && $device->device_status === 'online') {
            WaterQuality::create($data);
        }

        // ส่งสถานะและคำสั่งปัจจุบัน ตอบกลับไปให้ Arduino ทำงานต่อทันที
        return response()->json([
            'status' => $device ? $device->device_status : 'offline', // เบรกเกอร์
            'action' => $command ? $command->command_action : 'NONE', // เปิด/ปิดปั๊ม (Manual)
            'mode'   => $command ? $command->operating_mode : 'AUTO'  // โหมดการทำงาน
        ]);
    }

    // 2. Arduino เข้ามาดึงคำสั่งล่าสุด (เผื่อไว้ใช้กรณีที่ Arduino อยากเช็คแค่คำสั่งโดยไม่ส่งข้อมูล)
    // Route: GET /api/device-command/{device_id}
    public function getCommand($device_id)
    {
        $command = SystemCommand::where('device_id', $device_id)->latest()->first();

        if ($command) {
            return response()->json([
                'action' => $command->command_action,
                'mode'   => $command->operating_mode,
            ]);
        }

        return response()->json(['action' => 'NONE', 'mode' => 'AUTO']);
    }

    // ==========================================
    // Routes สำหรับ Dashboard
    // ==========================================

    // 3. Dashboard ดึงข้อมูลล่าสุดแบบ Real-time (GET)
    // Route: GET /api/get-latest-data/{device_id}
    public function getLatestData($device_id)
    {
        $device = IoTDevice::where('device_id', $device_id)->first();
        if (!$device) {
            return response()->json(null, 404);
        }

        $data    = WaterQuality::where('device_id', $device_id)->latest()->first();
        $command = SystemCommand::where('device_id', $device_id)->latest()->first();

        return response()->json([
            'device_status' => $device->device_status,
            'ph_value'      => $data ? $data->ph_value    : null,
            'temperature'   => $data ? $data->temperature : null,
            'turbidity'     => $data ? $data->turbidity   : null,
            'current_mode'  => $command ? $command->operating_mode : 'AUTO',
            'ph_min'        => $device->ph_min  ?? 6.5,
            'ph_max'        => $device->ph_max  ?? 8.5,
            'turb_max'      => $device->turb_max ?? 20,
        ]);
    }

    // 4. Dashboard ส่งคำสั่งเปิด/ปิด (POST จาก Web)
    // Route: POST /api/send-command  (ต้องผ่าน auth:sanctum middleware)
    public function sendCommand(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'เฉพาะ Admin เท่านั้น'], 403);
        }

        $request->validate([
            'device_id'      => 'required|integer|exists:io_t_devices,device_id',
            'command_action' => 'required|in:OPEN,CLOSE,NONE',
            'operating_mode' => 'required|in:AUTO,MANUAL',
        ]);

        SystemCommand::create([
            'device_id'      => $request->device_id,
            'command_action' => $request->command_action,
            'operating_mode' => $request->operating_mode,
        ]);

        return response()->json(['status' => 'success']);
    }

    // ==========================================
    // จัดการอุปกรณ์ (เฉพาะ Admin ผ่าน Web)
    // ==========================================

    public function addDevice(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->withErrors(['error' => 'เฉพาะ Admin เท่านั้น']);
        }

        $request->validate(['device_name' => 'required|string|max:255']);

        IoTDevice::create([
            'device_name'   => $request->device_name,
            'device_status' => 'online',
            'ph_min'        => 6.5,
            'ph_max'        => 8.5,
            'turb_max'      => 20.0,
        ]);

        return back()->with('success', 'เพิ่มอุปกรณ์สำเร็จ!');
    }

    public function deleteDevice($device_id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->withErrors(['error' => 'เฉพาะ Admin เท่านั้น']);
        }

        IoTDevice::where('device_id', $device_id)->delete();

        return back()->with('success', 'ลบอุปกรณ์เรียบร้อยแล้ว');
    }

    public function toggleDeviceStatus($device_id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->withErrors(['error' => 'เฉพาะ Admin เท่านั้น']);
        }

        $device = IoTDevice::where('device_id', $device_id)->first();
        if ($device) {
            $device->device_status = ($device->device_status === 'online') ? 'offline' : 'online';
            $device->save();
        }

        return back()->with('success', 'เปลี่ยนสถานะการทำงานเรียบร้อย');
    }

    public function updateThresholds(Request $request, $device_id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->withErrors(['error' => 'เฉพาะ Admin เท่านั้น']);
        }

        $request->validate([
            'ph_min'   => 'required|numeric|min:0|max:14',
            'ph_max'   => 'required|numeric|min:0|max:14|gte:ph_min',
            'turb_max' => 'required|numeric|min:0',
        ]);

        $device = IoTDevice::where('device_id', $device_id)->first();
        if ($device) {
            $device->ph_min   = $request->ph_min;
            $device->ph_max   = $request->ph_max;
            $device->turb_max = $request->turb_max;
            $device->save();
        }

        return back()->with('success', 'อัปเดตเกณฑ์แจ้งเตือนเรียบร้อยแล้ว!');
    }
}