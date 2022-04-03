<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposerItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposer_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('proposer_id')->nullable(false)->unsigned();
            $table->bigInteger('user_id')->nullable(false)->unsigned();
            $table->bigInteger('type_id')->nullable(false)->unsigned();

            $table->string('image_path')->nullable();
            $table->longText('html_content')->nullable();
            $table->bigInteger('product_id')->nullable()->unsigned();

            $table->timestamps();

            $table->foreign('proposer_id')
                ->references('id')->on('proposers')
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
        Schema::dropIfExists('proposer_items');
    }
}
