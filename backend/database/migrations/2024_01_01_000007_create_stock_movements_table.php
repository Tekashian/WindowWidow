<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out']); // przyjęcie / wydanie
            $table->decimal('quantity', 10, 2);
            $table->string('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('production_order_id')->nullable(); // bez constraint na razie
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
