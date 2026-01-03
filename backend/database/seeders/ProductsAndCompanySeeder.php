<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsAndCompanySeeder extends Seeder
{
    public function run(): void
    {
        // Company settings
        DB::table('company_settings')->insert([
            'company_name' => 'WindowWidow Sp. z o.o.',
            'tax_id' => '1234567890',
            'address' => 'ul. Produkcyjna 15',
            'city' => 'Warszawa',
            'postal_code' => '02-222',
            'phone' => '+48 22 123 45 67',
            'email' => 'biuro@windowwidow.pl',
            'warehouse_address' => 'ul. Magazynowa 8',
            'warehouse_city' => 'Warszawa',
            'warehouse_postal_code' => '02-333',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Products
        $products = [
            [
                'name' => 'Okno PVC białe 120x150 cm',
                'code' => 'WIN-PVC-001',
                'type' => 'window',
                'description' => 'Standardowe okno PVC z podwójną szybą, kolor biały, wymiary 120x150 cm',
                'default_specifications' => json_encode([
                    'width' => 120,
                    'height' => 150,
                    'material' => 'PVC',
                    'color' => 'Biały',
                    'glass_type' => 'Podwójna szyba',
                    'profile' => '5-komorowy'
                ]),
                'estimated_production_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Drzwi balkonowe PVC 90x220 cm',
                'code' => 'DOOR-PVC-001',
                'type' => 'door',
                'description' => 'Drzwi balkonowe PVC z szybą, kolor biały, wymiary 90x220 cm',
                'default_specifications' => json_encode([
                    'width' => 90,
                    'height' => 220,
                    'material' => 'PVC',
                    'color' => 'Biały',
                    'glass_type' => 'Podwójna szyba',
                    'handle' => 'Standardowy biały'
                ]),
                'estimated_production_days' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Panel szklany 100x200 cm',
                'code' => 'PANEL-GLASS-001',
                'type' => 'panel',
                'description' => 'Panel szklany do zabudowy, szkło hartowane, wymiary 100x200 cm',
                'default_specifications' => json_encode([
                    'width' => 100,
                    'height' => 200,
                    'material' => 'Szkło hartowane',
                    'thickness' => '8mm',
                    'finish' => 'Przezroczyste'
                ]),
                'estimated_production_days' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            $product['created_at'] = now();
            $product['updated_at'] = now();
            DB::table('products')->insert($product);
        }
    }
}
