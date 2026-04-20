<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemCommand extends Model
{
    // แก้ไข: เพิ่ม $fillable เพื่อป้องกัน MassAssignmentException
    // ก่อนหน้านี้ Model นี้ว่างเปล่า ทำให้ SystemCommand::create([...]) Error ทันที
    protected $fillable = [
        'device_id',
        'command_action',
        'operating_mode',
    ];

    // Relationship: คำสั่งนี้เป็นของอุปกรณ์ไหน
    public function device()
    {
        return $this->belongsTo(IoTDevice::class, 'device_id', 'device_id');
    }
}