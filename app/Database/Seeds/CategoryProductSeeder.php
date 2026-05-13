<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategoryProductSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Rice Meal'],
            ['name' => 'Main Dish'],
            ['name' => 'Noodles / Soup'],
            ['name' => 'Snacks'],
            ['name' => 'Beverages'],
            ['name' => 'Promos'],
        ];
        $this->db->table('categories')->insertBatch($categories);

        $cat = [
            'Rice Meal'     => 1,
            'Main Dish'     => 2,
            'Noodles / Soup'=> 3,
            'Snacks'        => 4,
            'Beverages'     => 5,
            'Promos'        => 6,
        ];

        $products = [
            // Rice Meal
            ['name' => 'Chicken Sisig',    'price' => 99.00,  'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Buttered Chicken', 'price' => 99.00,  'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Garlic Chicken',   'price' => 99.00,  'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Tocilog',          'price' => 99.00,  'stock' => 50, 'category_id' => $cat['Rice Meal']],
            ['name' => 'Chicken Teriyaki', 'price' => 99.00,  'stock' => 50, 'category_id' => $cat['Rice Meal']],

            // Main Dish
            ['name' => 'Garlic Chicken (Main)',       'price' => 130.00, 'stock' => 30, 'category_id' => $cat['Main Dish']],
            ['name' => 'Buttered Chicken (Main)',     'price' => 130.00, 'stock' => 30, 'category_id' => $cat['Main Dish']],
            ['name' => 'Chicharon Tilapia',           'price' => 130.00, 'stock' => 30, 'category_id' => $cat['Main Dish']],
            ['name' => 'Platter Chicken Sisig',       'price' => 130.00, 'stock' => 30, 'category_id' => $cat['Main Dish']],
            ['name' => 'Calamares',                   'price' => 130.00, 'stock' => 30, 'category_id' => $cat['Main Dish']],

            // Noodles / Soup
            ['name' => 'Beef Cheese Spaghetti',       'price' => 85.00,  'stock' => 40, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Chicken Carbonara / Bread',   'price' => 85.00,  'stock' => 40, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Sotanghon Guisado',           'price' => 150.00, 'stock' => 30, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Pancit Guisado',              'price' => 150.00, 'stock' => 30, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Lomi',                        'price' => 150.00, 'stock' => 30, 'category_id' => $cat['Noodles / Soup']],
            ['name' => 'Egg Drop',                    'price' => 60.00,  'stock' => 40, 'category_id' => $cat['Noodles / Soup']],

            // Snacks
            ['name' => 'French Fries',   'price' => 65.00,  'stock' => 50, 'category_id' => $cat['Snacks']],
            ['name' => 'Kikiam',         'price' => 50.00,  'stock' => 50, 'category_id' => $cat['Snacks']],
            ['name' => 'Squid Balls',    'price' => 50.00,  'stock' => 50, 'category_id' => $cat['Snacks']],
            ['name' => 'Fried Siomai',   'price' => 65.00,  'stock' => 50, 'category_id' => $cat['Snacks']],
            ['name' => 'Kropek',         'price' => 45.00,  'stock' => 50, 'category_id' => $cat['Snacks']],
            ['name' => 'Beef Nachos',    'price' => 130.00, 'stock' => 30, 'category_id' => $cat['Snacks']],

            // Beverages
            ['name' => 'Red Horse',        'price' => 160.00, 'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'San Mig Light',    'price' => 75.00,  'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'San Mig Flavored', 'price' => 75.00,  'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'SMB Pale Pilsen',  'price' => 75.00,  'stock' => 100, 'category_id' => $cat['Beverages']],
            ['name' => 'Fundador Light',   'price' => 450.00, 'stock' => 50,  'category_id' => $cat['Beverages']],
            ['name' => 'Coke 1.5 L',      'price' => 120.00, 'stock' => 50,  'category_id' => $cat['Beverages']],

            // Promos
            ['name' => 'Chicken Sisig Buy 1 Take 1', 'price' => 180.00, 'stock' => 999, 'category_id' => $cat['Promos']],
        ];

        $this->db->table('products')->insertBatch($products);
    }
}
