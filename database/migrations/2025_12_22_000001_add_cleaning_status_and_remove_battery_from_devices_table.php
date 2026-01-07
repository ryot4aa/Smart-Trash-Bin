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
        Schema::table('devices', function (Blueprint $table) {
            $table->enum('cleaning_status', ['sudah', 'belum'])->default('belum')->after('led_status');
            $table->dropColumn('battery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('cleaning_status');
            $table->unsignedTinyInteger('battery')->default(100)->comment('Persentase baterai (0-100)');
        });
    }
};
