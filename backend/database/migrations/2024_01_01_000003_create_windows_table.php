<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('windows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // np. "uchylne", "rozwierane", "stałe"
            $table->integer('width'); // szerokość w mm
            $table->integer('height'); // wysokość w mm
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('glass_id')->constrained('glasses')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('color')->default('bialy');
            $table->enum('status', ['projekt', 'w_produkcji', 'gotowe', 'wydane'])->default('projekt');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('windows');
    }
};
