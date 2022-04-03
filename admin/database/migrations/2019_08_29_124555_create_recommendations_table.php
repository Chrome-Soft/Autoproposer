<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecommendationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('segment_id')->nullable(false)->unsigned();
            $table->bigInteger('product_id')->nullable(false)->unsigned();

            $table->foreign('segment_id')
                ->references('id')->on('segments')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('product_id')
                ->references('id')->on('products')
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
        Schema::dropIfExists('recommendations');
    }
}
