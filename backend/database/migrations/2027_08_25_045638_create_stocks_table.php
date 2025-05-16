<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('unit_of_measure')->default('pc');
            $table->integer('physical_count');
            $table->integer('on_hand');
            $table->integer('sold');
            $table->decimal('price_per_unit', 10, 2);
            $table->decimal('buying_price', 10, 2);
            $table->date('date')->nullable();
            $table->date('date_released')->nullable();
            $table->string('receiver')->nullable();;
            $table->timestamp('time')->useCurrent();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
