<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Kouame Jean',
                'phone' => '+225 05 00 00 01',
                'email' => 'kouame.jean@email.com',
                'address' => 'Abidjan, Cocody',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Diallo Fatou',
                'phone' => '+225 05 00 00 02',
                'email' => 'fatou.diallo@email.com',
                'address' => 'Abidjan, Plateau',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Entreprise ABC',
                'phone' => '+225 05 00 00 03',
                'email' => 'contact@abc-entreprise.ci',
                'address' => 'Abidjan, Marcory Zone 4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('customers')->insert($customers);
    }
}
