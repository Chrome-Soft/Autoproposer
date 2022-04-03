<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidatorsToCriterias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $boolValidator = json_encode(['validators' => ['bool']]);
        $numberValidator = json_encode(['validators' => ['number']]);
        $versionValidator = json_encode(['validators' => ['version']]);
        $phoneProviderValidator = json_encode(['validators' => ['phone_provider']]);
        $datetimeValidator = json_encode(['validators' => ['datetime']]);

        DB::table('criterias')
            ->where('slug', 'device_is_mobile')
            ->update(['properties' => $boolValidator]);

        DB::table('criterias')
            ->whereIn('slug', ['device_memory', 'device_screen_width', 'device_screen_height', 'os_architecture', 'connection_bandwidth'])
            ->update(['properties' => $numberValidator]);

        DB::table('criterias')
            ->whereIn('slug', ['os_version', 'browser_version'])
            ->update(['properties' => $versionValidator]);

        DB::table('criterias')
            ->whereIn('slug', ['phone_provider'])
            ->update(['properties' => $phoneProviderValidator]);

        DB::table('criterias')
            ->whereIn('slug', ['created_at'])
            ->update(['properties' => $datetimeValidator]);
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
