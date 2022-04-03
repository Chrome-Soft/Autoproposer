<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageLoadsPartitions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_loads', function (Blueprint $table) {
            if (!\Illuminate\Support\Facades\App::environment('testing')) {
                DB::statement('ALTER TABLE `page_loads` MODIFY `created_at` DATETIME NOT NULL');
                DB::statement('ALTER TABLE `page_loads` DROP PRIMARY KEY, ADD PRIMARY KEY(`id`, `created_at`);');

                DB::statement('
                    ALTER TABLE `page_loads` PARTITION BY RANGE (MONTH(`created_at`))
                    (
                        PARTITION p_january VALUES LESS THAN (2),
                        PARTITION p_february VALUES LESS THAN (3),
                        PARTITION p_march VALUES LESS THAN (4),
                        PARTITION p_april VALUES LESS THAN (5),
                        PARTITION p_may VALUES LESS THAN (6),
                        PARTITION p_june VALUES LESS THAN (7),
                        PARTITION p_july VALUES LESS THAN (8),
                        PARTITION p_august VALUES LESS THAN (9),
                        PARTITION p_september VALUES LESS THAN (10),
                        PARTITION p_october VALUES LESS THAN (11),
                        PARTITION p_november VALUES LESS THAN (12),
                        PARTITION p_december VALUES LESS THAN MAXVALUE
                    )
                ');
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
        Schema::table('page_loads', function (Blueprint $table) {
            //
        });
    }
}
