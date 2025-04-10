<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up()
    {
        Schema::create('stock_log', function (Blueprint $table) {
            $table->id();
            $table->string('stock_id');
            $table->string('sku');
            $table->string('description')->nullable();
            $table->integer('qty');
            $table->string('reason');
            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_log');
    }
};


