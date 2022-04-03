<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegmentGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segment_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('segment_id');
            $table->string('bool_type')->nullable(true);

            $table->foreign('segment_id')
                ->references('id')->on('segments')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('segment_groups');
    }
}
