<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Window;
use App\Models\Profile;
use App\Models\Glass;

class WindowProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Pobierz przykładowe profile i szkła
        $profile = Profile::first();
        $glass = Glass::first();

        $windows = [
            [
                'name' => 'Okno Dachowe Premium',
                'type' => 'Dachowe',
                'width' => 780,
                'height' => 1400,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 1850.00,
                'status' => 'gotowe',
                'stock_quantity' => 15,
                'min_stock_level' => 5,
                'description' => 'Okno dachowe z energooszczędnym szkłem, idealne do poddaszy i mansard.'
            ],
            [
                'name' => 'Okno Ścienne Standard',
                'type' => 'Uchylno-rozwieralne',
                'width' => 1200,
                'height' => 1400,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 950.00,
                'status' => 'gotowe',
                'stock_quantity' => 45,
                'min_stock_level' => 10,
                'description' => 'Klasyczne okno ścienne z możliwością uchylania i rozwiерania.'
            ],
            [
                'name' => 'Okno Balkonowe',
                'type' => 'Balkonowe',
                'width' => 800,
                'height' => 2200,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 1650.00,
                'status' => 'gotowe',
                'stock_quantity' => 8,
                'min_stock_level' => 3,
                'description' => 'Wysokie okno balkonowe z niskim progiem, łatwy dostęp na balkon.'
            ],
            [
                'name' => 'Okno Stałe Panoramiczne',
                'type' => 'Stałe',
                'width' => 2400,
                'height' => 1800,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 2200.00,
                'status' => 'gotowe',
                'stock_quantity' => 5,
                'min_stock_level' => 2,
                'description' => 'Duże okno panoramiczne nieotwierane, maksymalizacja światła naturalnego.'
            ],
            [
                'name' => 'Okno Uchylne Łazienkowe',
                'type' => 'Uchylne',
                'width' => 600,
                'height' => 800,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 520.00,
                'status' => 'gotowe',
                'stock_quantity' => 28,
                'min_stock_level' => 8,
                'description' => 'Małe okno uchylne z matowym szkłem, idealne do łazienek.'
            ],
            [
                'name' => 'Okno Rozwieralne Kuchenne',
                'type' => 'Rozwieralne',
                'width' => 900,
                'height' => 1200,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 780.00,
                'status' => 'gotowe',
                'stock_quantity' => 32,
                'min_stock_level' => 10,
                'description' => 'Okno rozwieralne na zawiasach, łatwe czyszczenie od zewnątrz.'
            ]
        ];

        foreach ($windows as $windowData) {
            Window::create($windowData);
        }
    }
}
