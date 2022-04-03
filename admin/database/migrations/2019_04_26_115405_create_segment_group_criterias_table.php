<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegmentGroupCriteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segment_group_criterias', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('segment_group_id');
            $table->unsignedBigInteger('criteria_id');
            $table->unsignedBigInteger('relation_id');

            $table->text('value');
            $table->string('bool_type')->nullable(true);

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
        Schema::dropIfExists('segment_group_criterias');
    }
}
