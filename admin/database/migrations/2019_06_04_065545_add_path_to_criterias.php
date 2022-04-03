<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPathToCriterias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('criterias')->insert([
            [
                'name'  => 'Meglátogatott URL',
                'slug'  => 'visited_url'
            ],
            [
                'name'  => 'Bejárt URL útvonal',
                'slug'  => 'visited_path'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('criterias')->where('slug', 'visited_url')->delete();
        DB::table('criterias')->where('slug', 'visited_path')->delete();
    }
}
