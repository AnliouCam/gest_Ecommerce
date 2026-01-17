<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // PC Portables (category_id = 1)
            [
                'name' => 'HP Pavilion 15',
                'sku' => 'HP-PAV-15',
                'category_id' => 1,
                'purchase_price' => 350000,
                'sale_price' => 450000,
                'quantity' => 10,
                'image' => null,
                'max_discount' => 10,
                'stock_alert' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dell Inspiron 14',
                'sku' => 'DELL-INS-14',
                'category_id' => 1,
                'purchase_price' => 400000,
                'sale_price' => 520000,
                'quantity' => 8,
                'image' => null,
                'max_discount' => 15,
                'stock_alert' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lenovo ThinkPad E14',
                'sku' => 'LEN-TP-E14',
                'category_id' => 1,
                'purchase_price' => 500000,
                'sale_price' => 650000,
                'quantity' => 5,
                'image' => null,
                'max_discount' => 10,
                'stock_alert' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Telephones (category_id = 3)
            [
                'name' => 'Samsung Galaxy A54',
                'sku' => 'SAM-A54',
                'category_id' => 3,
                'purchase_price' => 180000,
                'sale_price' => 250000,
                'quantity' => 15,
                'image' => null,
                'max_discount' => 10,
                'stock_alert' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'iPhone 13',
                'sku' => 'IPH-13',
                'category_id' => 3,
                'purchase_price' => 450000,
                'sale_price' => 580000,
                'quantity' => 6,
                'image' => null,
                'max_discount' => 5,
                'stock_alert' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tecno Spark 10',
                'sku' => 'TEC-SP10',
                'category_id' => 3,
                'purchase_price' => 65000,
                'sale_price' => 95000,
                'quantity' => 20,
                'image' => null,
                'max_discount' => 15,
                'stock_alert' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Accessoires (category_id = 6)
            [
                'name' => 'Souris sans fil Logitech',
                'sku' => 'LOG-MSF',
                'category_id' => 6,
                'purchase_price' => 8000,
                'sale_price' => 15000,
                'quantity' => 30,
                'image' => null,
                'max_discount' => 20,
                'stock_alert' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Clavier USB HP',
                'sku' => 'HP-KB-USB',
                'category_id' => 6,
                'purchase_price' => 5000,
                'sale_price' => 10000,
                'quantity' => 25,
                'image' => null,
                'max_discount' => 20,
                'stock_alert' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Casque Bluetooth JBL',
                'sku' => 'JBL-BT-01',
                'category_id' => 6,
                'purchase_price' => 25000,
                'sale_price' => 45000,
                'quantity' => 12,
                'image' => null,
                'max_discount' => 15,
                'stock_alert' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Imprimantes (category_id = 5)
            [
                'name' => 'HP LaserJet Pro M404',
                'sku' => 'HP-LJ-M404',
                'category_id' => 5,
                'purchase_price' => 180000,
                'sale_price' => 250000,
                'quantity' => 4,
                'image' => null,
                'max_discount' => 10,
                'stock_alert' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Epson EcoTank L3250',
                'sku' => 'EPS-L3250',
                'category_id' => 5,
                'purchase_price' => 120000,
                'sale_price' => 175000,
                'quantity' => 6,
                'image' => null,
                'max_discount' => 10,
                'stock_alert' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Stockage (category_id = 7)
            [
                'name' => 'Cle USB 32GB Kingston',
                'sku' => 'KNG-USB-32',
                'category_id' => 7,
                'purchase_price' => 3000,
                'sale_price' => 6000,
                'quantity' => 50,
                'image' => null,
                'max_discount' => 20,
                'stock_alert' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Disque Dur Externe 1TB Seagate',
                'sku' => 'SEA-HDD-1T',
                'category_id' => 7,
                'purchase_price' => 35000,
                'sale_price' => 55000,
                'quantity' => 10,
                'image' => null,
                'max_discount' => 10,
                'stock_alert' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('products')->insert($products);
    }
}
