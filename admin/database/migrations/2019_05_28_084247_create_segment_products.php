<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegmentProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segment_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('segment_id')->unsigned()->nullable(false);
            $table->bigInteger('product_id')->unsigned()->nullable(false);
            $table->bigInteger('priority_id')->unsigned()->nullable(false);
            $table->bigInteger('user_id')->unsigned()->nullable(false);
            $table->timestamps();

            $table->unique(['product_id', 'segment_id']);

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('segment_id')
                ->references('id')->on('segments')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('priority_id')
                ->references('id')->on('segment_product_priorities')
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
        Schema::dropIfExists('segment_products');
    }
}
