<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertDefaultSegment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segments', function (Blueprint $table) {
//            $user = \App\User::where('email', env('ADMIN_EMAIL', 'admin@admin.hu'))->first();
            DB::table('segments')
                ->insert([
                    'name'          => 'Egyéb',
                    'slug'          => 'egyeb',
                    'user_id'       => null,
                    'created_at'    => \Carbon\Carbon::now(),
                    'is_default'    => 1
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
        Schema::table('segments', function (Blueprint $table) {
            DB::table('segments')->where('slug', 'egyeb')->delete();
        });
    }
}
