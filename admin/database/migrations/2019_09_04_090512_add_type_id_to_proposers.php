<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeIdToProposers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proposers', function (Blueprint $table) {
            $table->bigInteger('type_id')->nullable(true)->unsigned();

            $table->foreign('type_id')
                ->references('id')->on('proposer_types')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proposers', function (Blueprint $table) {
            //
        });
    }
}
