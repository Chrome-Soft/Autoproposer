<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposerItemPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposer_item_photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('proposer_item_id')->nullable(false)->unsigned();
            $table->bigInteger('user_id')->nullable(false)->unsigned();
            $table->string('image_path')->nullable(false);

            $table->timestamps();

            $table->foreign('proposer_item_id')
                ->references('id')->on('proposer_items')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposer_item_photos');
    }
}
