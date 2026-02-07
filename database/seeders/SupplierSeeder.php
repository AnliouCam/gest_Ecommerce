<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Tech Import SARL',
                'phone' => '+225 07 00 00 01',
                'email' => 'contact@techimport.ci',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Digital Store',
                'phone' => '+225 07 00 00 02',
                'email' => 'info@digitalstore.ci',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HP Distributeur',
                'phone' => '+225 07 00 00 03',
                'email' => 'ventes@hp-distrib.ci',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Samsung CI',
                'phone' => '+225 07 00 00 04',
                'email' => 'pro@samsung.ci',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('suppliers')->insert($suppliers);
    }
}
