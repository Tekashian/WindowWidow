<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('window_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->enum('status', ['oczekujace', 'w_realizacji', 'zakonczone'])->default('oczekujace');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_items');
    }
};
