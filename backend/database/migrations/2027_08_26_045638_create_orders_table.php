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
            $table->foreignId('customer_order_id')->nullable()->constrained('customer_orders')->onDelete('cascade'); // ✅ Now nullable
            $table->string('stock_id');
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price_per_unit', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
