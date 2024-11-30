<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('item_id');
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('unit_of_measure')->default('piece');
            $table->unsignedBigInteger('supplier_id');
            $table->text('description')->nullable();
            $table->timestamp('time')->useCurrent();

            $table->foreign(columns: 'item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};
