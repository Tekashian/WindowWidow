<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\Glass;

class ProfilesGlassesSeeder extends Seeder
{
    public function run(): void
    {
        // Profiles
        $profiles = [
            ['name' => 'Rehau Synego 80', 'manufacturer' => 'Rehau', 'type' => '6-komorowy', 'material' => 'PVC', 'color' => 'Biały', 'price_per_meter' => 89.99, 'is_active' => true],
            ['name' => 'Veka Softline 82', 'manufacturer' => 'Veka', 'type' => '6-komorowy', 'material' => 'PVC', 'color' => 'Biały', 'price_per_meter' => 85.50, 'is_active' => true],
            ['name' => 'Aluplast Ideal 8000', 'manufacturer' => 'Aluplast', 'type' => '7-komorowy', 'material' => 'PVC', 'color' => 'Antracyt', 'price_per_meter' => 95.00, 'is_active' => true],
        ];

        foreach ($profiles as $profile) {
            Profile::updateOrCreate(['name' => $profile['name']], $profile);
        }

        // Glasses
        $glasses = [
            ['name' => 'Dwuszybowe 4-16-4', 'type' => 'Energooszczędne', 'thickness' => 24, 'u_value' => 1.1, 'price_per_sqm' => 95.00, 'description' => 'Standardowe szkło dwuszybowe', 'is_active' => true],
            ['name' => 'Trójszybowe 4-12-4-12-4', 'type' => 'Energooszczędne Premium', 'thickness' => 36, 'u_value' => 0.7, 'price_per_sqm' => 145.00, 'description' => 'Trójszybowe o najwyższej izolacyjności', 'is_active' => true],
            ['name' => 'Antywłamaniowe P4A', 'type' => 'Bezpieczeństwa', 'thickness' => 28, 'u_value' => 1.0, 'price_per_sqm' => 180.00, 'description' => 'Szkło antywłamaniowe P4A', 'is_active' => true],
        ];

        foreach ($glasses as $glass) {
            Glass::updateOrCreate(['name' => $glass['name']], $glass);
        }
    }
}
