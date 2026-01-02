<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Window;
use App\Models\Profile;
use App\Models\Glass;

class RealisticWindowProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Pobierz profile i szkła
        $profile = Profile::first();
        $glass = Glass::first();

        $products = [
            [
                'name' => 'Okno PCV Dwuskrzydłowe Uchylno-Rozwieralne',
                'type' => 'Uchylno-rozwieralne',
                'width' => 1465,
                'height' => 1435,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 1289.00,
                'status' => 'gotowe',
                'stock_quantity' => 24,
                'min_stock_level' => 8,
                'description' => 'Klasyczne okno dwuskrzydłowe z profilu 5-komorowego. Jedno skrzydło uchylne, drugie uchylno-rozwieralne. Energooszczędne, współczynnik U=0,9 W/m²K. Idealne do pokoi dziennych i sypialni. Wyposażone w mikrouchył i nawiewniki. Kolor biały, możliwość okleiny w odcieniach drewna.',
                'image_url' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2YzZjRmNiIvPjxyZWN0IHg9IjIwIiB5PSIyMCIgd2lkdGg9IjE3MCIgaGVpZ2h0PSIzNjAiIGZpbGw9IiNmZmZmZmYiIHN0cm9rZT0iIzM0NDk1ZSIgc3Ryb2tlLXdpZHRoPSIzIi8+PHJlY3QgeD0iMjEwIiB5PSIyMCIgd2lkdGg9IjE3MCIgaGVpZ2h0PSIzNjAiIGZpbGw9IiNmZmZmZmYiIHN0cm9rZT0iIzM0NDk1ZSIgc3Ryb2tlLXdpZHRoPSIzIi8+PHJlY3QgeD0iMzAiIHk9IjMwIiB3aWR0aD0iMTUwIiBoZWlnaHQ9IjM0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjY2JkNWUxIiBzdHJva2Utd2lkdGg9IjIiLz48cmVjdCB4PSIyMjAiIHk9IjMwIiB3aWR0aD0iMTUwIiBoZWlnaHQ9IjM0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjY2JkNWUxIiBzdHJva2Utd2lkdGg9IjIiLz48Y2lyY2xlIGN4PSIyNDAiIGN5PSIyMDAiIHI9IjEyIiBmaWxsPSIjMzQ0OTVlIi8+PHBhdGggZD0iTTM1LDIwNSBMMzUsMjE1IiBzdHJva2U9IiMzNDQ5NWUiIHN0cm9rZS13aWR0aD0iMiIvPjwvc3ZnPg=='
            ],
            [
                'name' => 'Drzwi Balkonowe HST (Podnoszono-Przesuwne)',
                'type' => 'Balkonowe',
                'width' => 2700,
                'height' => 2180,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 5890.00,
                'status' => 'gotowe',
                'stock_quantity' => 6,
                'min_stock_level' => 2,
                'description' => 'Nowoczesne drzwi tarasowe HST z systemem podnoszono-przesuwnym. Duża przeszklona powierzchnia zapewnia maksymalny dopływ światła. Niska szyna progowa (20mm) - bezpieczna i komfortowa. Idealne do tarasów i ogrodów zimowych. 3-szybowy pakiet z ciepłą ramką. Bezobsługowe rolki z łożyskami kulkowymi. Wbudowane samozamykanie.',
                'image_url' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iI2UyZThmMCIvPjxyZWN0IHg9IjIwIiB5PSI1MCIgd2lkdGg9IjE4MCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNmZmZmZmYiIHN0cm9rZT0iIzM0NDk1ZSIgc3Ryb2tlLXdpZHRoPSI0Ii8+PHJlY3QgeD0iMTgwIiB5PSI1MCIgd2lkdGg9IjIwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNmZmZmZmYiIHN0cm9rZT0iIzM0NDk1ZSIgc3Ryb2tlLXdpZHRoPSI0Ii8+PHJlY3QgeD0iMzAiIHk9IjYwIiB3aWR0aD0iMTYwIiBoZWlnaHQ9IjI4MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjY2JkNWUxIiBzdHJva2Utd2lkdGg9IjIiLz48cmVjdCB4PSIxOTAiIHk9IjYwIiB3aWR0aD0iMTgwIiBoZWlnaHQ9IjI4MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjY2JkNWUxIiBzdHJva2Utd2lkdGg9IjIiLz48Y2lyY2xlIGN4PSIzNTAiIGN5PSIyMDAiIHI9IjE1IiBmaWxsPSIjMzQ0OTVlIi8+PHBhdGggZD0iTTIwLDM2MCBMMzgwLDM2MCIgc3Ryb2tlPSIjMzQ0OTVlIiBzdHJva2Utd2lkdGg9IjYiLz48L3N2Zz4='
            ],
            [
                'name' => 'Okno Dachowe Obrotowe 78x140',
                'type' => 'Dachowe',
                'width' => 780,
                'height' => 1400,
                'profile_id' => $profile?->id,
                'glass_id' => $glass?->id,
                'price' => 1650.00,
                'status' => 'gotowe',
                'stock_quantity' => 12,
                'min_stock_level' => 4,
                'description' => 'Wysokiej jakości okno dachowe typu Velux do dachów o nachyleniu 15-90°. Sterowanie górne z uchwytem obrotowym. Podwójnie hartowane szkło P2A z powłoką Low-E. Wentylacja w każdej pozycji skrzydła. Kołnierz universalny w zestawie. Zabezpieczenie przed wypadnięciem. Łatwy montaż - pasuje do wszystkich materiałów pokryciowych.',
                'image_url' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9InNreSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiM4N2NlZWI7c3RvcC1vcGFjaXR5OjEiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNlMGY3ZmE7c3RvcC1vcGFjaXR5OjEiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0idXJsKCNza3kpIi8+PHBhdGggZD0iTTUwLDUwIEwzNTAsMTAwIEwzNTAsMzUwIEw1MCwzMDAgWiIgZmlsbD0iI2ZmZmZmZiIgc3Ryb2tlPSIjMzQ0OTVlIiBzdHJva2Utd2lkdGg9IjUiLz48cGF0aCBkPSJNNjAsNjUgTDM0MCwxMTUgTDM0MCwzMzUgTDYwLDI4NSBaIiBmaWxsPSJub25lIiBzdHJva2U9IiNjYmQ1ZTEiIHN0cm9rZS13aWR0aD0iMiIvPjxwYXRoIGQ9Ik0yMDAsNTAgTDIwMCwzNTAiIHN0cm9rZT0iIzk0YTNiOCIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtZGFzaGFycmF5PSI1LDUiLz48Y2lyY2xlIGN4PSIzMjAiIGN5PSIxNzAiIHI9IjEwIiBmaWxsPSIjMzQ0OTVlIi8+PC9zdmc+'
            ]
        ];

        foreach ($products as $productData) {
            Window::create($productData);
        }
    }
}
