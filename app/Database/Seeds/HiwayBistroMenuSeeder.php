<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * HI-WAY BISTRO menu: replaces categories and products.
 * Excludes KTV Room from menu; keeps "KTV Room Charge" product for system use.
 */
class HiwayBistroMenuSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // WARNING: This will clear inventory and order-item history.
        // Needed to avoid foreign key errors when resetting the menu.
        if ($db->tableExists('stock_logs')) {
            $db->table('stock_logs')->emptyTable();
        }
        if ($db->tableExists('order_items')) {
            $db->table('order_items')->emptyTable();
        }
        $db->table('products')->emptyTable();
        $db->table('categories')->emptyTable();

        $categories = [
            ['name' => 'Rice Meal'],
            ['name' => 'Main Dish'],
            ['name' => 'Noodles / Soup'],
            ['name' => 'Snacks'],
            ['name' => 'Beverages'],
            ['name' => 'Others'],
        ];
        $db->table('categories')->insertBatch($categories);

        $cat = [];
        foreach ($db->table('categories')->select('id, name')->get()->getResultArray() as $row) {
            $cat[$row['name']] = (int) $row['id'];
        }

        $products = [
            // Rice Meal
            ['name' => 'Chicken Sisig', 'price' => 99, 'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Buttered Chicken', 'price' => 99, 'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Garlic Chicken', 'price' => 99, 'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Tocilog', 'price' => 99, 'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Chicken Teriyaki', 'price' => 99, 'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Chicken Sisig Buy 1 Take 1', 'price' => 180, 'stock' => 30, 'category_id' => $cat['Rice Meal']],
            // Main Dish
            ['name' => 'Garlic Chicken (Main)', 'price' => 130, 'stock' => 40, 'category_id' => $cat['Main Dish']],
            ['name' => 'Buttered Chicken (Main)', 'price' => 130, 'stock' => 40, 'category_id' => $cat['Main Dish']],
            ['name' => 'Chicharon Tilapia', 'price' => 130, 'stock' => 40, 'category_id' => $cat['Main Dish']],
            ['name' => 'Platter Chicken Sisig', 'price' => 130, 'stock' => 40, 'category_id' => $cat['Main Dish']],
            ['name' => 'Calamares', 'price' => 130, 'stock' => 40, 'category_id' => $cat['Main Dish']],
            // Noodles / Soup
            ['name' => 'Beef Cheese Spaghetti', 'price' => 85, 'stock' => 40, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Chicken Carbonara / Bread', 'price' => 85, 'stock' => 40, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Sotanghon Guisado', 'price' => 150, 'stock' => 40, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Pancit Guisado', 'price' => 150, 'stock' => 40, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Lomi', 'price' => 150, 'stock' => 40, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Egg Drop', 'price' => 60, 'stock' => 50, 'category_id' => $cat['Noodles / Soup']],
            // Snacks
            ['name' => 'French Fries', 'price' => 65, 'stock' => 60, 'category_id' => $cat['Snacks']],
            ['name' => 'Kikiam', 'price' => 50, 'stock' => 60, 'category_id' => $cat['Snacks']],
            ['name' => 'Squid Balls', 'price' => 50, 'stock' => 60, 'category_id' => $cat['Snacks']],
            ['name' => 'Fried Siomai', 'price' => 65, 'stock' => 60, 'category_id' => $cat['Snacks']],
            ['name' => 'Kropek', 'price' => 45, 'stock' => 60, 'category_id' => $cat['Snacks']],
            ['name' => 'Beef Natchos', 'price' => 130, 'stock' => 40, 'category_id' => $cat['Snacks']],
            // Beverages
            ['name' => 'Red Horse', 'price' => 160, 'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'San Mig Light', 'price' => 75, 'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'San Mig Light Bucket', 'price' => 360, 'stock' => 50, 'category_id' => $cat['Beverages']],
            ['name' => 'San Mig Flavored', 'price' => 75, 'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'San Mig Flavored Bucket', 'price' => 360, 'stock' => 50, 'category_id' => $cat['Beverages']],
            ['name' => 'SMB Pale Pilsen', 'price' => 75, 'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'SMB Pale Pilsen Bucket', 'price' => 360, 'stock' => 50, 'category_id' => $cat['Beverages']],
            ['name' => 'Fundador Light', 'price' => 450, 'stock' => 30, 'category_id' => $cat['Beverages']],
            ['name' => 'Coke 1.5 L', 'price' => 120, 'stock' => 80, 'category_id' => $cat['Beverages']],
            // Others (system use only - for KTV room charge)
            ['name' => 'KTV Room Charge', 'price' => 0, 'stock' => 9999, 'category_id' => $cat['Others']],
        ];

        $db->table('products')->insertBatch($products);
    }
}
