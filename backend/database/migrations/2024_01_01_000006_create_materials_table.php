<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['profil', 'szyba', 'okucie', 'uszczelka', 'inne']);
            $table->string('unit'); // m, m2, szt, kg
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('min_stock', 10, 2)->default(0);
            $table->decimal('price_per_unit', 10, 2);
            $table->string('supplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
