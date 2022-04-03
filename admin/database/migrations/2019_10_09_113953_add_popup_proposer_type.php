<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPopupProposerType extends Migration
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
                    'name'  => 'Pop-up',
                    'key'   => 'popup'
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
        Schema::table('proposer_types', function (Blueprint $table) {
            //
        });
    }
}
