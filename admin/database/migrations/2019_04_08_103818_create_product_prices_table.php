<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id')->unsigned()->nullable(false);
            $table->bigInteger('currency_id')->unsigned()->nullable(false);
            $table->bigInteger('user_id')->unsigned()->nullable(false);
            $table->float('price')->unsigned()->nullable(false);
            $table->timestamps();

            $table->unique(['product_id', 'currency_id']);

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('currency_id')
                ->references('id')->on('currencies')
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
        Schema::dropIfExists('product_prices');
    }
}
