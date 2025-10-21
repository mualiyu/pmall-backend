<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'store_id' => 'STORE-001',
                'category_id' => 1,
                'sub_category_id' => 2,
                'brand_id' => 1,
                'name' => 'Wireless Bluetooth Headphones',
                'image' => 'headphones.jpg',
                'description' => 'Comfortable over-ear wireless Bluetooth headphones with noise cancellation and long battery life.',
                'cost_price' => 25000,
                'selling_price' => 30000,
                'inStock' => 1,
                'quantity' => 50,
                'tags' => 'electronics,audio,headphones',
                'status' => 'active',
                'more_images' => json_encode(['headphones_side.jpg', 'headphones_back.jpg']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'store_id' => 'STORE-001',
                'category_id' => 1,
                'sub_category_id' => 3,
                'brand_id' => 2,
                'name' => 'Smartphone X10',
                'image' => 'smartphone.jpg',
                'description' => 'Latest smartphone with 8GB RAM, 256GB storage, and AMOLED display.',
                'cost_price' => 180000,
                'selling_price' => 200000,
                'inStock' => 1,
                'quantity' => 30,
                'tags' => 'electronics,phone,smartphone',
                'status' => 'active',
                'more_images' => json_encode(['smartphone_front.jpg', 'smartphone_back.jpg']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'store_id' => 'STORE-002',
                'category_id' => 2,
                'sub_category_id' => null,
                'brand_id' => 3,
                'name' => 'Men’s Running Shoes',
                'image' => 'shoes.jpg',
                'description' => 'Lightweight running shoes designed for comfort and speed.',
                'cost_price' => 12000,
                'selling_price' => 15000,
                'inStock' => 1,
                'quantity' => 100,
                'tags' => 'fashion,men,shoes',
                'status' => 'active',
                'more_images' => json_encode(['shoes_side.jpg', 'shoes_top.jpg']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
