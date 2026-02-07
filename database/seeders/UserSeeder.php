<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Gerant Boutique',
            'email' => 'gerant@boutique.com',
            'password' => Hash::make('password'),
            'role' => 'gerant',
        ]);

        User::create([
            'name' => 'Vendeur 1',
            'email' => 'vendeur@boutique.com',
            'password' => Hash::make('password'),
            'role' => 'vendeur',
        ]);
    }
}
