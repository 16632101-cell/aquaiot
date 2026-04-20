<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterQuality extends Model
{
    // อนุญาตให้ Arduino ส่งค่าเหล่านี้มาบันทึกได้ (แก้ Error 500 Mass Assignment)
    protected $fillable = [
        'device_id', 
        'ph_value', 
        'turbidity', 
        'temperature'
    ];
}