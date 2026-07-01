<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WaterQuality;
use App\Models\SystemCommand;
use App\Models\IoTDevice;

class IoTController extends Controller
{
    // API สำหรับ Arduino (รับข้อมูลและส่งคำสั่งกลับ)
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

        // 🌟 ดึงคำสั่งมาเก็บในตัวแปร
        $actionToSend = $command ? $command->command_action : 'NONE';
        $modeToSend = $command ? $command->operating_mode : 'AUTO';

        // 🌟 ถ้าระบบสั่ง OPEN (ปล่อยสาร) ให้เคลียร์ค่าเป็น NONE ทันที เพื่อกัน Servo ทำงานซ้ำ
        if ($actionToSend === 'OPEN' && $command) {
            $command->update(['command_action' => 'NONE']);
        }

        return response()->json([
            'status' => $device ? $device->device_status : 'offline', 
            'action' => $actionToSend, 
            'mode'   => $modeToSend  
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

    // ฟังก์ชันอื่นๆ (getLatestData, sendCommand, addDevice, deleteDevice, toggleDeviceStatus, updateThresholds) 
    // ให้คงไว้เหมือนเดิมได้เลยครับ ไม่ต้องแก้ไข
    
    public function getLatestData($device_id)
    {
        $device = IoTDevice::where('device_id', $device_id)->first();
        if (!$device) return response()->json(null, 404);

        $data = WaterQuality::where('device_id', $device_id)->latest()->first();
        $command = SystemCommand::where('device_id', $device_id)->first();
        $history = WaterQuality::where('device_id', $device_id)->latest()->take(60)->get()->reverse()->values();
        $alerts = WaterQuality::where('device_id', $device_id)
                    ->where(function($query) use ($device) {
                        $query->where('ph_value', '<', $device->ph_min)
                              ->orWhere('ph_value', '>', $device->ph_max)
                              ->orWhere('turbidity', '>', $device->turb_max);
                    })->latest()->take(5)->get();

        return response()->json([
            'device_status' => $device->device_status,
            'ph_value'      => $data ? $data->ph_value : null,
            'temperature'   => $data ? $data->temperature : null,
            'turbidity'     => $data ? $data->turbidity : null,
            'current_mode'  => $command ? $command->operating_mode : 'AUTO',
            'ph_min'        => $device->ph_min ?? 6.5,
            'ph_max'        => $device->ph_max ?? 8.5,
            'turb_max'      => $device->turb_max ?? 20,
            'history'       => $history, 
            'alerts'        => $alerts   
        ]);
    }

    public function sendCommand(Request $request)
    {
        if (auth()->user()->role !== 'admin') return response()->json(['status' => 'error'], 403);
        $request->validate([
            'device_id'      => 'required|integer|exists:io_t_devices,device_id',
            'command_action' => 'required|in:OPEN,CLOSE,NONE',
            'operating_mode' => 'required|in:AUTO,MANUAL',
        ]);

        SystemCommand::updateOrCreate(
            ['device_id' => $request->device_id],
            ['command_action' => $request->command_action, 'operating_mode' => $request->operating_mode]
        );
        return response()->json(['status' => 'success']);
    }

    // ... (ส่วน addDevice, deleteDevice, toggleDeviceStatus, updateThresholds คงเดิม)
}