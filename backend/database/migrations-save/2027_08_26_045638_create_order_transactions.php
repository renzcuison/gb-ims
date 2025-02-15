<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransactions extends Migration
{
    public function up()
    {
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_id');
            $table->string('item_name');
            $table->string('item_sku');
            $table->integer('quantity'); 
            $table->string('reason'); 
            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('item_name')->references('item_name')->on('stocks')->onDelete('cascade');
            $table->foreign('item_sku')->references('sku')->on('skus')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    

    public function down()
    {
        Schema::dropIfExists('order_transactions');
    }
}