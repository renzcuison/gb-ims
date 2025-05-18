<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('customer_orders', function (Blueprint $table) {
        $table->string('verified_by')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('customer_orders', function (Blueprint $table) {
        $table->dropColumn('verified_by');
    });
}

};
