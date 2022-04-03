<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageLoadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_loads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('cookie_id');
            $table->uuid('partner_external_id')->nullable(false);

            $table->string('from_url');
            $table->string('to_url');

            $table->string('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_loads');
    }
}
