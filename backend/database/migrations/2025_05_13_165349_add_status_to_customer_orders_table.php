<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->string('status')->default('Pending'); // Add status column with default 'Pending'
        });
    }

    public function down()
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn('status'); // Drop status column if rolling back
        });
    }
};
