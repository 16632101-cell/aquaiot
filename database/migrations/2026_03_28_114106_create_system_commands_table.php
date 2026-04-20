<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_commands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('command_action')->default('NONE'); // OPEN, CLOSE, NONE
            $table->string('operating_mode')->default('AUTO'); // AUTO, MANUAL
            $table->timestamps();

            // เชื่อมกับตาราง io_t_devices ถ้าลบอุปกรณ์ คำสั่งจะถูกลบตามด้วย
            $table->foreign('device_id')
                  ->references('device_id')
                  ->on('io_t_devices')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_commands');
    }
};