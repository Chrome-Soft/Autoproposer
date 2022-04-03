<?php

use App\Segment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertDefaultSequences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segments', function (Blueprint $table) {
            $segments = Segment::orderBy('id')->get();
            foreach ($segments as $i => $segment) {
                $segment->sequence = $i;
                $segment->save();
            }
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
