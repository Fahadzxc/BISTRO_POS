<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategoryProductSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Food'],
            ['name' => 'Drinks'],
            ['name' => 'Alcohol'],
            ['name' => 'Promo'],
            ['name' => 'Others'],
        ];
        $this->db->table('categories')->insertBatch($categories);

        $categoryIds = [
            'Food'    => 1,
            'Drinks'  => 2,
            'Alcohol' => 3,
            'Promo'   => 4,
            'Others'  => 5,
        ];

        $products = [
            ['name' => 'Chicken Adobo', 'price' => 120.00, 'stock' => 50, 'category_id' => $categoryIds['Food']],
            ['name' => 'Pancit Canton', 'price' => 80.00, 'stock' => 40, 'category_id' => $categoryIds['Food']],
            ['name' => 'Beef Tapa', 'price' => 150.00, 'stock' => 30, 'category_id' => $categoryIds['Food']],
            ['name' => 'Halo-Halo', 'price' => 75.00, 'stock' => 60, 'category_id' => $categoryIds['Food']],
            ['name' => 'Coke', 'price' => 35.00, 'stock' => 100, 'category_id' => $categoryIds['Drinks']],
            ['name' => 'Sprite', 'price' => 35.00, 'stock' => 100, 'category_id' => $categoryIds['Drinks']],
            ['name' => 'Water', 'price' => 25.00, 'stock' => 150, 'category_id' => $categoryIds['Drinks']],
            ['name' => 'Iced Tea', 'price' => 45.00, 'stock' => 80, 'category_id' => $categoryIds['Drinks']],
            ['name' => 'San Miguel Beer', 'price' => 60.00, 'stock' => 120, 'category_id' => $categoryIds['Alcohol']],
            ['name' => 'Red Horse', 'price' => 70.00, 'stock' => 100, 'category_id' => $categoryIds['Alcohol']],
            ['name' => 'Tanduay Ice', 'price' => 55.00, 'stock' => 90, 'category_id' => $categoryIds['Alcohol']],
            ['name' => 'Happy Hour', 'price' => 299.00, 'stock' => 999, 'category_id' => $categoryIds['Promo']],
            ['name' => 'KTV Package A', 'price' => 999.00, 'stock' => 999, 'category_id' => $categoryIds['Promo']],
            ['name' => 'KTV Room Charge', 'price' => 0, 'stock' => 9999, 'category_id' => $categoryIds['Others']],
        ];
        $this->db->table('products')->insertBatch($products);
    }
}
