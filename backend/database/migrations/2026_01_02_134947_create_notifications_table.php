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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // production, warehouse, admin, system
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like IDs, links
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->string('icon')->nullable(); // Emoji or icon name
            $table->string('link')->nullable(); // URL to navigate
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
