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
            // Customer information
            $table->string('customer_name')->nullable()->after('source_id');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');
            
            // Delivery address
            $table->text('delivery_address')->nullable()->after('customer_email');
            $table->string('delivery_city')->nullable()->after('delivery_address');
            $table->string('delivery_postal_code')->nullable()->after('delivery_city');
            $table->text('delivery_notes')->nullable()->after('delivery_postal_code');
            
            // Product details
            $table->string('product_type')->nullable()->after('delivery_notes'); // e.g., "Window", "Door", "Glass Panel"
            $table->text('product_description')->nullable()->after('product_type');
            $table->integer('quantity')->default(1)->after('product_description');
            $table->json('product_specifications')->nullable()->after('quantity'); // dimensions, materials, colors, etc.
            
            // Production status tracking
            $table->boolean('confirmed_by_production')->default(false)->after('status');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by_production');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->onDelete('set null');
            
            // Delay tracking
            $table->boolean('is_delayed')->default(false)->after('estimated_completion_at');
            $table->text('delay_reason')->nullable()->after('is_delayed');
            $table->timestamp('revised_completion_at')->nullable()->after('delay_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'customer_email',
                'delivery_address',
                'delivery_city',
                'delivery_postal_code',
                'delivery_notes',
                'product_type',
                'product_description',
                'quantity',
                'product_specifications',
                'confirmed_by_production',
                'confirmed_at',
                'confirmed_by',
                'is_delayed',
                'delay_reason',
                'revised_completion_at',
            ]);
        });
    }
};
