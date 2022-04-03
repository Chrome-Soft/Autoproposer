<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTemplatePhotoNameToPhotoPath extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segment_appearance_templates', function (Blueprint $table) {
            $table->renameColumn('photo_name', 'photo_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment_appearance_templates', function (Blueprint $table) {
            //
        });
    }
}
