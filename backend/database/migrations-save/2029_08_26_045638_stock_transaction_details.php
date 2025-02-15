<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stock_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('stock_id');
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->decimal('price_per_unit', 10, 2);
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('stock_transactions')->onDelete('cascade');
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transaction_details');
    }
};
