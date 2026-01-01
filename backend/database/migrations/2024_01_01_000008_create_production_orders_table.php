<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->enum('status', ['nowe', 'w_trakcie', 'zakonczone', 'anulowane'])->default('nowe');
            $table->enum('priority', ['niska', 'normalna', 'wysoka', 'pilne'])->default('normalna');
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
