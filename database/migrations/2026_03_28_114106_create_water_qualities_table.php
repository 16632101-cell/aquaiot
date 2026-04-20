<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('water_qualities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->float('ph_value');
            $table->float('turbidity');
            $table->float('temperature');
            $table->timestamps();
            $table->foreign('device_id')->references('device_id')->on('io_t_devices')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('water_qualities'); }
};