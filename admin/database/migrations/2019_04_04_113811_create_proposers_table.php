<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->float('width');
            $table->float('height');
            $table->string('page_url');
            $table->integer('max_item_number');
            $table->bigInteger('partner_id')->unsigned()->nullable(false);
            $table->bigInteger('user_id')->unsigned()->nullable(false);

            $table->foreign('partner_id')
                ->references('id')->on('partners')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposers');
    }
}
