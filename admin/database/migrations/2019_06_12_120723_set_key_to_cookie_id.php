<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetKeyToCookieId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Illuminate\Support\Facades\App::environment('testing')) {
            DB::statement('ALTER TABLE `user_data` ADD KEY `cookie_id_user_data` (`cookie_id`)');
            DB::statement('ALTER TABLE `page_loads` ADD KEY `cookie_id_page_loads` (`cookie_id`)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
