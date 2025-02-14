<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('item_id');
            $table->integer('quantity');
            $table->decimal('item_price_per_unit', 10, 2);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('stocks')->onDelete('cascade');
        });
    }



    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
