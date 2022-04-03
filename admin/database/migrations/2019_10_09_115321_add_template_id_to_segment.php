<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTemplateIdToSegment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segments', function (Blueprint $table) {
            $table->bigInteger('template_id')->unsigned()->nullable()->default(null);

            $table->foreign('template_id')
                ->references('id')->on('segment_appearance_templates')
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
        Schema::table('segments', function (Blueprint $table) {
            //
        });
    }
}
