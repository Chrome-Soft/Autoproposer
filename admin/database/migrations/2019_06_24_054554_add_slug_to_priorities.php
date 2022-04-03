<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlugToPriorities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segment_product_priorities', function (Blueprint $table) {
            $table->text('slug')->nullable(true);
        });

        \Illuminate\Support\Facades\DB::table('segment_product_priorities')
            ->where('id', '=', \App\SegmentProductPriority::ALWAYS_PRESENT)
            ->update(['slug' => 'always']);

        \Illuminate\Support\Facades\DB::table('segment_product_priorities')
            ->where('id', '=', \App\SegmentProductPriority::OPTIONAL_PRESENT)
            ->update(['slug' => 'optional']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment_product_priorities', function (Blueprint $table) {
            //
        });
    }
}
