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
        Schema::table('windows', function (Blueprint $table) {
            // Dodaj tylko kolumny których nie ma
            
            // Depth (nie istnieje)
            if (!Schema::hasColumn('windows', 'depth')) {
                $table->decimal('depth', 8, 2)->nullable()->after('height')->comment('Depth/thickness in mm');
            }
            
            // Model information (nie istnieją)
            if (!Schema::hasColumn('windows', 'model')) {
                $table->string('model')->nullable()->after('type');
                $table->string('manufacturer')->nullable()->after('model');
            }
            
            // Technical specifications (nie istnieją)
            if (!Schema::hasColumn('windows', 'chambers')) {
                $table->integer('chambers')->nullable()->after('color')->comment('Number of chambers in profile');
                $table->decimal('thermal_coefficient', 5, 3)->nullable()->after('chambers')->comment('U-value');
                $table->integer('sound_insulation')->nullable()->after('thermal_coefficient')->comment('Rw in dB');
            }
            
            // Features (nie istnieje)
            if (!Schema::hasColumn('windows', 'features')) {
                $table->json('features')->nullable()->after('sound_insulation')->comment('Additional features');
            }
            
            // Pricing (nie istnieją - price już istnieje ale dodamy bazowe ceny)
            if (!Schema::hasColumn('windows', 'base_price')) {
                $table->decimal('base_price', 10, 2)->nullable()->after('price');
                $table->decimal('installation_price', 10, 2)->nullable()->after('base_price');
            }
            
            // is_featured (is_active już istnieje)
            if (!Schema::hasColumn('windows', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
            
            // SEO & categorization (nie istnieją)
            if (!Schema::hasColumn('windows', 'sku')) {
                $table->string('sku')->unique()->nullable()->after('is_featured');
                $table->string('category')->nullable()->after('sku');
                $table->json('tags')->nullable()->after('category');
            }
        });
        
        // Zmień width i height z integer na decimal dla większej precyzji
        DB::statement('ALTER TABLE windows MODIFY COLUMN width DECIMAL(8, 2) NOT NULL COMMENT "Width in mm"');
        DB::statement('ALTER TABLE windows MODIFY COLUMN height DECIMAL(8, 2) NOT NULL COMMENT "Height in mm"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('windows', function (Blueprint $table) {
            if (Schema::hasColumn('windows', 'depth')) {
                $table->dropColumn('depth');
            }
            if (Schema::hasColumn('windows', 'model')) {
                $table->dropColumn(['model', 'manufacturer']);
            }
            if (Schema::hasColumn('windows', 'chambers')) {
                $table->dropColumn(['chambers', 'thermal_coefficient', 'sound_insulation']);
            }
            if (Schema::hasColumn('windows', 'features')) {
                $table->dropColumn('features');
            }
            if (Schema::hasColumn('windows', 'base_price')) {
                $table->dropColumn(['base_price', 'installation_price']);
            }
            if (Schema::hasColumn('windows', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('windows', 'sku')) {
                $table->dropColumn(['sku', 'category', 'tags']);
            }
        });
        
        // Przywróć integer dla width i height
        DB::statement('ALTER TABLE windows MODIFY COLUMN width INT NOT NULL');
        DB::statement('ALTER TABLE windows MODIFY COLUMN height INT NOT NULL');
    }
};
