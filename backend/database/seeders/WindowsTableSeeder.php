<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Window;
use App\Models\Profile;
use App\Models\Glass;

class WindowsTableSeeder extends Seeder
{
    public function run(): void
    {
        $profile = Profile::first();
        $glass = Glass::first();

        if (!$profile || !$glass) {
            $this->command->warn('Profiles and glasses must be seeded first!');
            return;
        }

        $windows = [
            ['name' => 'Okno PCV Standard 1200x1400', 'type' => 'Uchylno-rozwieralne', 'width' => 1200, 'height' => 1400, 'profile_id' => $profile->id, 'glass_id' => $glass->id, 'price' => 850.00, 'description' => 'Standardowe okno PCV dwuskrzydłowe', 'stock_quantity' => 15, 'min_stock_level' => 5, 'is_active' => true],
            ['name' => 'Okno PCV Balkonowe 800x2200', 'type' => 'Uchylne', 'width' => 800, 'height' => 2200, 'profile_id' => $profile->id, 'glass_id' => $glass->id, 'price' => 950.00, 'description' => 'Wysokie okno balkonowe', 'stock_quantity' => 8, 'min_stock_level' => 3, 'is_active' => true],
            ['name' => 'Okno PCV Małe 600x800', 'type' => 'Uchylne', 'width' => 600, 'height' => 800, 'profile_id' => $profile->id, 'glass_id' => $glass->id, 'price' => 450.00, 'description' => 'Małe okno łazienkowe', 'stock_quantity' => 25, 'min_stock_level' => 10, 'is_active' => true],
        ];

        foreach ($windows as $window) {
            Window::updateOrCreate(['name' => $window['name']], $window);
        }
    }
}
