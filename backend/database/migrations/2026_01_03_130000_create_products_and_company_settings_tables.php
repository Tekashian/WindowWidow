<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Okno PVC 120x150"
            $table->string('code')->unique(); // e.g., "WIN-PVC-001"
            $table->enum('type', ['window', 'door', 'panel', 'balcony']);
            $table->text('description');
            $table->json('default_specifications')->nullable(); // wymiary, materiały domyślne
            $table->integer('estimated_production_days')->default(7); // szacowany czas produkcji w dniach
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('tax_id')->nullable(); // NIP
            $table->string('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('phone');
            $table->string('email');
            $table->string('warehouse_address');
            $table->string('warehouse_city');
            $table->string('warehouse_postal_code');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('company_settings');
    }
};
