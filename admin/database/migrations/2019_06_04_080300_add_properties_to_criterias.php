<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPropertiesToCriterias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('criterias', function (Blueprint $table) {
            $table->longText('properties')->nullable(true);
        });

        DB::table('criterias')
            ->where('slug', 'visited_url')
            ->update([
                'properties'    => json_encode([
                    'elems' => [
                        ['type' => 'select', 'name' => 'from', 'classes' => ['form-control']]
                    ]
                ])
            ]);

        DB::table('criterias')
            ->where('slug', 'visited_path')
            ->update([
                'properties'    => json_encode([
                    'elems' => [
                        ['type' => 'select', 'name' => 'from', 'classes' => ['form-control']],
                        ['type' => 'select', 'name' => 'to', 'classes' => ['form-control']]
                    ]
                ])
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('criterias', function (Blueprint $table) {
            //
        });
    }
}
