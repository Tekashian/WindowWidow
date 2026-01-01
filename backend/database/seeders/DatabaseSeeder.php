<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Glass;
use App\Models\Window;
use App\Models\Material;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Użytkownicy
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@okna.pl',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $magazynier = User::create([
            'name' => 'Jan Kowalski',
            'email' => 'magazyn@okna.pl',
            'password' => Hash::make('magazyn123'),
            'role' => 'magazynier',
        ]);

        $produkcja = User::create([
            'name' => 'Piotr Nowak',
            'email' => 'produkcja@okna.pl',
            'password' => Hash::make('produkcja123'),
            'role' => 'produkcja',
        ]);

        // Profile
        $profile1 = Profile::create([
            'name' => 'VEKA Softline 70',
            'manufacturer' => 'VEKA',
            'type' => 'PVC',
            'material' => 'PVC',
            'color' => 'Biały',
            'price_per_meter' => 45.50,
        ]);

        $profile2 = Profile::create([
            'name' => 'Aluplast Ideal 4000',
            'manufacturer' => 'Aluplast',
            'type' => 'PVC',
            'material' => 'PVC',
            'color' => 'Brąz',
            'price_per_meter' => 52.00,
        ]);

        $profile3 = Profile::create([
            'name' => 'Reynaers CS 77',
            'manufacturer' => 'Reynaers',
            'type' => 'Aluminium',
            'material' => 'Aluminium',
            'color' => 'Antracyt',
            'price_per_meter' => 125.00,
        ]);

        // Szkła
        $glass1 = Glass::create([
            'name' => 'Dwuszybowe 4/16/4',
            'type' => 'dwuszybowe',
            'thickness' => 24,
            'u_value' => 1.1,
            'price_per_sqm' => 85.00,
            'description' => 'Standardowe szkło energooszczędne',
        ]);

        $glass2 = Glass::create([
            'name' => 'Trzyszybowe 4/14/4/14/4',
            'type' => 'trzyszybowe',
            'thickness' => 40,
            'u_value' => 0.7,
            'price_per_sqm' => 145.00,
            'description' => 'Wysokoenergooszczędne szkło',
        ]);

        $glass3 = Glass::create([
            'name' => 'Antywłamaniowe P4A',
            'type' => 'dwuszybowe',
            'thickness' => 30,
            'u_value' => 1.0,
            'price_per_sqm' => 195.00,
            'description' => 'Szkło bezpieczeństwa',
        ]);

        // Okna
        Window::create([
            'name' => 'Okno uchylno-rozwierane 1200x1400',
            'type' => 'uchylno-rozwierane',
            'width' => 1200,
            'height' => 1400,
            'profile_id' => $profile1->id,
            'glass_id' => $glass1->id,
            'color' => 'biały',
            'price' => 890.00,
            'status' => 'projekt',
            'description' => 'Standardowe okno PVC',
        ]);

        Window::create([
            'name' => 'Okno stałe 1000x1000',
            'type' => 'stałe',
            'width' => 1000,
            'height' => 1000,
            'profile_id' => $profile1->id,
            'glass_id' => $glass2->id,
            'color' => 'biały',
            'price' => 650.00,
            'status' => 'projekt',
        ]);

        Window::create([
            'name' => 'Okno ALU 1500x2000',
            'type' => 'uchylno-rozwierane',
            'width' => 1500,
            'height' => 2000,
            'profile_id' => $profile3->id,
            'glass_id' => $glass3->id,
            'color' => 'antracyt',
            'price' => 2450.00,
            'status' => 'projekt',
        ]);

        Window::create([
            'name' => 'Okno PCV 800x1200 brąz',
            'type' => 'rozwierne',
            'width' => 800,
            'height' => 1200,
            'profile_id' => $profile2->id,
            'glass_id' => $glass1->id,
            'color' => 'brąz',
            'price' => 720.00,
            'status' => 'w_produkcji',
        ]);

        // Materiały magazynowe
        Material::create([
            'name' => 'Profil PVC VEKA 70mm biały',
            'type' => 'profil',
            'unit' => 'm',
            'current_stock' => 500.00,
            'min_stock' => 100.00,
            'price_per_unit' => 45.50,
            'supplier' => 'VEKA Polska',
        ]);

        Material::create([
            'name' => 'Profil ALU Reynaers antracyt',
            'type' => 'profil',
            'unit' => 'm',
            'current_stock' => 150.00,
            'min_stock' => 50.00,
            'price_per_unit' => 125.00,
            'supplier' => 'Reynaers',
        ]);

        Material::create([
            'name' => 'Szyba dwuszybowa 4/16/4',
            'type' => 'szyba',
            'unit' => 'm²',
            'current_stock' => 85.00,
            'min_stock' => 20.00,
            'price_per_unit' => 85.00,
            'supplier' => 'Guardian Glass',
        ]);

        Material::create([
            'name' => 'Szyba trzyszybowa 4/14/4/14/4',
            'type' => 'szyba',
            'unit' => 'm²',
            'current_stock' => 45.00,
            'min_stock' => 15.00,
            'price_per_unit' => 145.00,
            'supplier' => 'Guardian Glass',
        ]);

        Material::create([
            'name' => 'Klamka okienna Roto',
            'type' => 'okucie',
            'unit' => 'szt',
            'current_stock' => 250.00,
            'min_stock' => 50.00,
            'price_per_unit' => 45.00,
            'supplier' => 'Roto Frank',
        ]);

        Material::create([
            'name' => 'Zawias okienny Roto',
            'type' => 'okucie',
            'unit' => 'szt',
            'current_stock' => 180.00,
            'min_stock' => 40.00,
            'price_per_unit' => 32.00,
            'supplier' => 'Roto Frank',
        ]);

        Material::create([
            'name' => 'Uszczelka EPDM czarna',
            'type' => 'uszczelka',
            'unit' => 'm',
            'current_stock' => 15.00, // Niski stan!
            'min_stock' => 50.00,
            'price_per_unit' => 8.50,
            'supplier' => 'Deventer',
        ]);

        Material::create([
            'name' => 'Silikon montażowy',
            'type' => 'inne',
            'unit' => 'szt',
            'current_stock' => 35.00,
            'min_stock' => 20.00,
            'price_per_unit' => 12.50,
            'supplier' => 'Soudal',
        ]);

        echo "✅ Seedowanie zakończone!\n";
        echo "👤 Admin: admin@okna.pl / admin123\n";
        echo "📦 Magazynier: magazyn@okna.pl / magazyn123\n";
        echo "🔧 Produkcja: produkcja@okna.pl / produkcja123\n";
    }
}
