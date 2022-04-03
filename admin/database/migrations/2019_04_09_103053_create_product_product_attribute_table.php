<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductProductAttributeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_product_attribute', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id')->nullable(false)->unsigned();
            $table->bigInteger('product_attribute_id')->nullable(false)->unsigned();
            $table->text('value')->nullable(false);
            $table->timestamps();

            $table->unique(['product_id', 'product_attribute_id']);

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('product_attribute_id')
                ->references('id')->on('product_attributes')
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
        Schema::dropIfExists('product_product_attribute');
    }
}
