<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IoTDevice extends Model
{
    use HasFactory;

    // บังคับให้ Laravel รู้ว่าเราใช้ device_id เป็นคีย์หลัก
    protected $primaryKey = 'device_id';

    // อนุญาตให้แก้ไขข้อมูลเหล่านี้ได้
    protected $fillable = [
        'device_name', 
        'location', 
        'device_status', 
        'ph_min', 
        'ph_max', 
        'turb_max'
    ];
}