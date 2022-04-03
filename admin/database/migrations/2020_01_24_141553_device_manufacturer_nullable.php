<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeviceManufacturerNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('user_data')
            ->whereNull('device_manufacturer')
            ->update(['device_manufacturer' => '']);

        Schema::table('user_data', function (Blueprint $table) {
            $table->string('device_manufacturer')->nullable(false)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
