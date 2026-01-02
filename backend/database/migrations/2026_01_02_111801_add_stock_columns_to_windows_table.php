<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('windows', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->after('status');
            $table->integer('min_stock_level')->default(5)->after('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('windows', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'min_stock_level']);
        });
    }
};
