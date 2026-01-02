<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('production_orders', 'source_type')) {
                // Source information
                $table->enum('source_type', ['customer_order', 'stock_replenishment'])->after('order_number');
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            
            if (!Schema::hasColumn('production_orders', 'estimated_completion_at')) {
                // Timeline tracking
                $table->timestamp('estimated_completion_at')->nullable()->after('started_at');
                $table->timestamp('actual_completion_at')->nullable()->after('estimated_completion_at');
            }
            
            if (!Schema::hasColumn('production_orders', 'created_by')) {
                // Audit trail
                $table->foreignId('created_by')->after('notes')->constrained('users')->onDelete('cascade');
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
        });
        
        // Update existing status enum to English
        DB::statement("ALTER TABLE production_orders MODIFY COLUMN status ENUM('pending', 'materials_check', 'materials_reserved', 'in_progress', 'quality_check', 'completed', 'shipped_to_warehouse', 'delivered', 'cancelled', 'on_hold') DEFAULT 'pending'");
        
        // Update existing priority enum to English
        DB::statement("ALTER TABLE production_orders MODIFY COLUMN priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            if (Schema::hasColumn('production_orders', 'source_type')) {
                $table->dropColumn(['source_type', 'source_id']);
            }
            if (Schema::hasColumn('production_orders', 'estimated_completion_at')) {
                $table->dropColumn(['estimated_completion_at', 'actual_completion_at']);
            }
            if (Schema::hasColumn('production_orders', 'created_by')) {
                $table->dropColumn(['created_by', 'updated_by']);
            }
        });
        
        // Revert to Polish enums
        DB::statement("ALTER TABLE production_orders MODIFY COLUMN status ENUM('nowe', 'w_trakcie', 'zakonczone', 'anulowane') DEFAULT 'nowe'");
        DB::statement("ALTER TABLE production_orders MODIFY COLUMN priority ENUM('niska', 'normalna', 'wysoka', 'pilne') DEFAULT 'normalna'");
    }
};
