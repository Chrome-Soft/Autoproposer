<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_terms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('search_term')->nullable(false);
            $table->string('cookie_id')->nullable(false);
            $table->uuid('partner_external_id')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_terms');
    }
}
