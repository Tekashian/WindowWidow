<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glasses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // np. "jednoszybowe", "dwuszybowe", "trzyszybowe"
            $table->integer('thickness'); // grubość w mm
            $table->decimal('u_value', 5, 2); // współczynnik przenikania ciepła
            $table->decimal('price_per_sqm', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glasses');
    }
};
