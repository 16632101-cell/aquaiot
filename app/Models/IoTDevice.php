<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoTDevice extends Model
{
    protected $primaryKey = 'device_id';
    
    // เพิ่ม ph_min, ph_max, turb_max เพื่อให้บันทึกลงฐานข้อมูลได้
    protected $fillable = [
        'device_name', 
        'location', 
        'device_status', 
        'ph_min', 
        'ph_max', 
        'turb_max'
    ];
}