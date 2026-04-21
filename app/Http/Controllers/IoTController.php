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

    public function storeData(Request $request)
    {
        if (\App\Models\IoTDevice::exists()) {
            $request->merge(['device_id' => \App\Models\IoTDevice::first()->device_id]);
        }

        $data = $request->validate([
            'device_id'   => 'required|integer|exists:io_t_devices,device_id',
            'ph_value'    => 'required|numeric',
            'turbidity'   => 'required|numeric',
            'temperature' => 'required|numeric',
        ]);

        $device = IoTDevice::where('device_id', $data['device_id'])->first();
        $command = SystemCommand::where('device_id', $data['device_id'])->first();

        if ($device && $device->device_status === 'online') {
            WaterQuality::create($data);
        }

        return response()->json([
            'status' => $device ? $device->device_status : 'offline', 
            'action' => $command ? $command->command_action : 'NONE', 
            'mode'   => $command ? $command->operating_mode : 'AUTO'  
        ]);
    }

    public function getCommand($device_id)
    {
        $command = SystemCommand::where('device_id', $device_id)->first();

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

    public function getLatestData($device_id)
    {
        $device = IoTDevice::where('device_id', $device_id)->first();
        if (!$device) {
            return response()->json(null, 404);
        }

        $data    = WaterQuality::where('device_id', $device_id)->latest()->first();
        $command = SystemCommand::where('device_id', $device_id)->first();

        // 🌟 ดึงข้อมูลย้อนหลัง 60 รายการ (เพื่อให้กราฟมันลากซูมดูระยะเวลาได้เยอะขึ้น)
        $history = WaterQuality::where('device_id', $device_id)
                    ->latest()->take(60)->get()->reverse()->values();

        $alerts = WaterQuality::where('device_id', $device_id)
                    ->where(function($query) use ($device) {
                        $query->where('ph_value', '<', $device->ph_min)
                              ->orWhere('ph_value', '>', $device->ph_max)
                              ->orWhere('turbidity', '>', $device->turb_max);
                    })
                    ->latest()->take(5)->get();

        return response()->json([
            'device_status' => $device->device_status,
            'ph_value'      => $data ? $data->ph_value    : null,
            'temperature'   => $data ? $data->temperature : null,
            'turbidity'     => $data ? $data->turbidity   : null,
            'current_mode'  => $command ? $command->operating_mode : 'AUTO',
            'ph_min'        => $device->ph_min  ?? 6.5,
            'ph_max'        => $device->ph_max  ?? 8.5,
            'turb_max'      => $device->turb_max ?? 20,
            'history'       => $history, 
            'alerts'        => $alerts   
        ]);
    }

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

        SystemCommand::updateOrCreate(
            ['device_id'      => $request->device_id],
            [
                'command_action' => $request->command_action,
                'operating_mode' => $request->operating_mode,
            ]
        );

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
        SystemCommand::where('device_id', $device_id)->delete();

        return back()->with('success', 'ลบอุปกรณ์เรียบร้อยแล้ว');
    }

    public function toggleDeviceStatus($device_id)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->withErrors(['error' => 'เฉพาะ Admin เท่านั้น']);
        }

        $device = IoTDevice::where('device_id', $device_id)->first();
        if ($device) {
            $newStatus = ($device->device_status === 'online') ? 'offline' : 'online';
            IoTDevice::where('device_id', $device_id)->update(['device_status' => $newStatus]);
        }

        return back()->with('success', 'เปลี่ยนสถานะการทำงานเรียบร้อย');
    }

    public function updateThresholds(Request $request, $device_id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'เฉพาะ Admin เท่านั้น'], 403);
        }

        $request->validate([
            'ph_min'   => 'required|numeric|min:0|max:14',
            'ph_max'   => 'required|numeric|min:0|max:14|gte:ph_min',
            'turb_max' => 'required|numeric|min:0',
        ], [
            'ph_max.gte' => '⚠️ ค่า "pH สูงสุด" ต้องมีค่ามากกว่าหรือเท่ากับ "pH ต่ำสุด" นะครับ!'
        ]);

        $device = IoTDevice::findOrFail($device_id);
        
        $device->update([
            'ph_min'   => $request->ph_min,
            'ph_max'   => $request->ph_max,
            'turb_max' => $request->turb_max
        ]);

        return response()->json(['status' => 'success', 'message' => 'อัปเดตเกณฑ์แจ้งเตือนเรียบร้อยแล้ว!']);
    }
}