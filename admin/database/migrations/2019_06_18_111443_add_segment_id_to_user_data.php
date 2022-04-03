<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSegmentIdToUserData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_data', function (Blueprint $table) {
            $table->bigInteger('segment_id')->nullable(true);

            $table->index('segment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_data', function (Blueprint $table) {
            $table->removeColumn('segment_id');
        });
    }
}
