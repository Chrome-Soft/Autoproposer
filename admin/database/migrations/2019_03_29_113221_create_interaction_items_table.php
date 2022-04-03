<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInteractionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interaction_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('interaction_id')->nullable(false);
            $table->string('item_name')->nullable(false);
            $table->string('item_id')->nullable(false);

            $table->float('buy_quantity')->nullable(true);
            $table->float('buy_unit_price')->nullable(true);

            $table->foreign('interaction_id')
                ->references('id')->on('interactions')
                ->onDelete('no action')
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
        Schema::dropIfExists('interaction_items');
    }
}
