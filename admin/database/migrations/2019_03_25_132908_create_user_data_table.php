<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('device_manufacturer')->nullable();
            $table->string('device_product')->nullable();
            $table->boolean('device_is_mobile');
            $table->float('device_memory')->nullable()->default(null);

            $table->float('device_screen_width')->nullable()->default(null);
            $table->float('device_screen_height')->nullable()->default(null);

            $table->integer('os_architecture')->nullable()->default(null);
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();

            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->text('browser_user_agent')->nullable();
            $table->string('browser_language')->nullable();

            $table->float('connection_bandwidth')->nullable()->default(null);
            $table->string('connection_ip_address')->nullable();
            $table->string('connection_effective_type')->nullable();
            $table->string('connection_real_type')->nullable();

            $table->integer('timezone_offset')->nullable();

            $table->string('location_country_code')->nullable();
            $table->string('location_country_name')->nullable();
            $table->string('location_city_name')->nullable();
            $table->string('location_postal_code')->nullable();
            $table->string('location_subdivision_name')->nullable();
            $table->string('location_subdivision_code')->nullable();
            $table->float('location_latitude')->nullable();
            $table->float('location_longitude')->nullable();

            $table->string('email_domain')->nullable();
            $table->string('phone_provider')->nullable();
            $table->date('birth_date')->nullable()->default(null);
            $table->string('sex')->nullable()->default(null);

            $table->string('location_real_city_name')->nullable()->default(null);
            $table->string('location_real_postal_code')->nullable()->default(null);

            $table->string('user_id')->nullable();
            $table->uuid('partner_external_id')->nullable(false);

            $table->string('cookie_id')->unique();

//            $table->foreign('partner_external_id')
//                ->references('external_id')->on('partners')
//                ->onDelete('cascade')
//                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_data');
    }
}
