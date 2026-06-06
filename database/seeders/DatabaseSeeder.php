<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@milkteashop.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create Cashier User
        User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@milkteashop.com',
            'password' => Hash::make('password123'),
            'role' => 'cashier',
        ]);

        // Create Milk Tea Products
        $milkTeas = [
            ['name' => 'Classic Milk Tea', 'price' => 65.00, 'stock' => 100],
            ['name' => 'Brown Sugar Milk Tea', 'price' => 75.00, 'stock' => 80],
            ['name' => 'Thai Milk Tea', 'price' => 70.00, 'stock' => 90],
            ['name' => 'Matcha Milk Tea', 'price' => 80.00, 'stock' => 70],
            ['name' => 'Taro Milk Tea', 'price' => 75.00, 'stock' => 85],
            ['name' => 'Chocolate Milk Tea', 'price' => 70.00, 'stock' => 95],
            ['name' => 'Strawberry Milk Tea', 'price' => 75.00, 'stock' => 75],
            ['name' => 'Oreo Milk Tea', 'price' => 85.00, 'stock' => 60],
        ];

        foreach ($milkTeas as $tea) {
            Product::create([
                'name' => $tea['name'],
                'category' => 'milk_tea',
                'price' => $tea['price'],
                'stock' => $tea['stock'],
                'description' => "Delicious {$tea['name']} made with premium ingredients",
                'is_active' => true,
            ]);
        }

        // Create Toppings
        $toppings = [
            ['name' => 'Tapioca Pearls (Boba)', 'price' => 15.00, 'stock' => 200],
            ['name' => 'Coconut Jelly', 'price' => 10.00, 'stock' => 150],
            ['name' => 'Grass Jelly', 'price' => 10.00, 'stock' => 150],
            ['name' => 'Pudding', 'price' => 15.00, 'stock' => 120],
            ['name' => 'Aloe Vera', 'price' => 12.00, 'stock' => 100],
            ['name' => 'Red Beans', 'price' => 12.00, 'stock' => 100],
            ['name' => 'Sago', 'price' => 10.00, 'stock' => 180],
            ['name' => 'Nata de Coco', 'price' => 12.00, 'stock' => 130],
        ];

        foreach ($toppings as $topping) {
            Product::create([
                'name' => $topping['name'],
                'category' => 'topping',
                'price' => $topping['price'],
                'stock' => $topping['stock'],
                'description' => "Premium {$topping['name']} topping",
                'is_active' => true,
            ]);
        }

        // Create Supplies
        $supplies = [
            ['name' => 'Plastic Cup (16oz)', 'price' => 3.00, 'stock' => 500],
            ['name' => 'Plastic Cup (22oz)', 'price' => 4.00, 'stock' => 400],
            ['name' => 'Cup Lid', 'price' => 1.00, 'stock' => 1000],
            ['name' => 'Straw (Regular)', 'price' => 0.50, 'stock' => 2000],
            ['name' => 'Straw (Jumbo for Boba)', 'price' => 1.00, 'stock' => 1500],
            ['name' => 'Paper Bag (Small)', 'price' => 5.00, 'stock' => 300],
            ['name' => 'Paper Bag (Large)', 'price' => 8.00, 'stock' => 200],
            ['name' => 'Napkin', 'price' => 0.50, 'stock' => 3000],
        ];

        foreach ($supplies as $supply) {
            Product::create([
                'name' => $supply['name'],
                'category' => 'supply',
                'price' => $supply['price'],
                'stock' => $supply['stock'],
                'description' => "Quality {$supply['name']} for your shop",
                'is_active' => true,
            ]);
        }

        // Backfill supply mappings for milk tea / coffee / fruit products
        $this->call(ProductSuppliesSeeder::class);
    }
}