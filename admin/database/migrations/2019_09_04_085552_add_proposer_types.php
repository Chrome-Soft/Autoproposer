<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProposerTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proposer_types', function (Blueprint $table) {
            DB::table('proposer_types')
                ->insert([
                    'name'  => 'iFrame',
                    'key'   => 'iframe'
                ]);

            DB::table('proposer_types')
                ->insert([
                    'name'  => 'Oldalba ágyazott elem',
                    'key'   => 'embedded'
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
