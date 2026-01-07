<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->unsignedTinyInteger('volume')->comment('Volume sampah dalam persen (0-100)');
            $table->unsignedTinyInteger('gas')->nullable()->comment('Level gas/bau sensor');
            $table->timestamp('reading_time')->useCurrent()->comment('Waktu pembacaan sensor');
            $table->timestamps();
            $table->index('device_id');
            $table->index('reading_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
