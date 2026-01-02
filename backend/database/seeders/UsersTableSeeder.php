<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@windowwidow.pl'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin'
            ]
        );

        // Production user
        User::updateOrCreate(
            ['email' => 'produkcja@windowwidow.pl'],
            [
                'name' => 'Jan Kowalski',
                'password' => Hash::make('prod123'),
                'role' => 'produkcja'
            ]
        );

        // Warehouse user
        User::updateOrCreate(
            ['email' => 'magazyn@windowwidow.pl'],
            [
                'name' => 'Anna Nowak',
                'password' => Hash::make('mag123'),
                'role' => 'magazynier'
            ]
        );
    }
}
