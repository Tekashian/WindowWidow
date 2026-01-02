<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialsTableSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['name' => 'Profil PVC biały 6m', 'type' => 'profil', 'unit' => 'mb', 'current_stock' => 450.00, 'min_stock' => 100.00, 'price_per_unit' => 89.99, 'supplier' => 'Rehau Polska', 'is_active' => true],
            ['name' => 'Szkło energooszczędne 4-16-4', 'type' => 'szyba', 'unit' => 'm²', 'current_stock' => 85.50, 'min_stock' => 50.00, 'price_per_unit' => 95.00, 'supplier' => 'Guardian Glass', 'is_active' => true],
            ['name' => 'Klamka Hoppe Atlanta', 'type' => 'okucie', 'unit' => 'szt', 'current_stock' => 120.00, 'min_stock' => 30.00, 'price_per_unit' => 35.00, 'supplier' => 'Hoppe Polska', 'is_active' => true],
            ['name' => 'Uszczelka EPDM czarna', 'type' => 'uszczelka', 'unit' => 'mb', 'current_stock' => 580.00, 'min_stock' => 200.00, 'price_per_unit' => 4.50, 'supplier' => 'Deventer', 'is_active' => true],
            ['name' => 'Profil aluminiowy antracyt', 'type' => 'profil', 'unit' => 'mb', 'current_stock' => 15.00, 'min_stock' => 50.00, 'price_per_unit' => 125.00, 'supplier' => 'Aluprof', 'is_active' => true],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(['name' => $material['name']], $material);
        }
    }
}
