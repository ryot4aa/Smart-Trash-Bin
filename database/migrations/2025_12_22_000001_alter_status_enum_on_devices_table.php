<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStatusEnumOnDevicesTable extends Migration
{
    public function up()
    {
        // Ubah enum status menjadi ['pending', 'online', 'offline']
        \DB::statement("ALTER TABLE devices MODIFY status ENUM('pending', 'online', 'offline') DEFAULT 'pending'");
    }

    public function down()
    {
        // Kembalikan ke enum awal ['online', 'offline']
        \DB::statement("ALTER TABLE devices MODIFY status ENUM('online', 'offline') DEFAULT 'offline'");
    }
}
