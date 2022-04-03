<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable(false)->unique();
            $table->string('slug')->nullable(false)->unique();
            $table->bigInteger('type_id')->nullable(false)->unsigned();
            $table->bigInteger('user_id')->nullable(true)->unsigned();

//            $table->foreign('type_id')
//                ->references('id')->on('product_attributes')
//                ->onUpdate('cascade')
//                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_attributes');
    }
}
