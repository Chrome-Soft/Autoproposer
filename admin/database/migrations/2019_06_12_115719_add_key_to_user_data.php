<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeyToUserData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Illuminate\Support\Facades\App::environment('testing')) {
            DB::statement('ALTER TABLE `user_data` ADD KEY `id_user_data` (`id`)');
            DB::statement('ALTER TABLE `page_loads` ADD KEY `id_page_loads` (`id`)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_data', function (Blueprint $table) {
            //
        });
    }
}
