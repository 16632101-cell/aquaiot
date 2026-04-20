<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('io_t_devices', function (Blueprint $table) {
            $table->id('device_id');
            $table->string('device_name');
            $table->string('location')->nullable();
            $table->string('device_status')->default('online'); // online, offline

            // เกณฑ์แจ้งเตือนแยกต่างหากแต่ละอุปกรณ์
            $table->float('ph_min')->default(6.5);
            $table->float('ph_max')->default(8.5);
            $table->float('turb_max')->default(20.0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('io_t_devices');
    }
};