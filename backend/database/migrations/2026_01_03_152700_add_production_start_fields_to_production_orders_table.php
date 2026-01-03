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
        Schema::table('production_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('started_by')->nullable()->after('started_at');
            $table->integer('production_time_hours')->nullable()->after('started_by');
            $table->dateTime('estimated_warehouse_delivery_date')->nullable()->after('production_time_hours');
            
            $table->foreign('started_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['started_by']);
            $table->dropColumn(['started_by', 'production_time_hours', 'estimated_warehouse_delivery_date']);
        });
    }
};
