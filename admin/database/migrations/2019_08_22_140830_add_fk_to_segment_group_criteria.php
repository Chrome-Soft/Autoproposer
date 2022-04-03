<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToSegmentGroupCriteria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::raw("
            delete from segment_group_criterias sgc
            left join segment_groups sg
            on sgc.segment_group_id = sg.id
            where sg.id is null
        ");

        Schema::table('segment_group_criterias', function (Blueprint $table) {
            $table->foreign('segment_group_id')
                ->references('id')->on('segment_groups')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('criteria_id')
                ->references('id')->on('criterias')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('relation_id')
                ->references('id')->on('relations')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment_group_criterias', function (Blueprint $table) {
            //
        });
    }
}
