<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'PC Portables', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PC Bureau', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Telephones', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tablettes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Imprimantes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accessoires', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stockage', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reseau', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('categories')->insert($categories);
    }
}
