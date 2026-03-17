<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Role;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Manage products, staff, and reports.',
                'is_active' => true,
            ],
        );

        $cashierRole = Role::query()->updateOrCreate(
            ['slug' => 'cashier'],
            [
                'name' => 'Cashier',
                'description' => 'Handle orders and payment operations.',
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate([
            'email' => 'admin@coffee.test',
        ], [
            'name' => 'Admin User',
            'password' => 'password123',
            'role_id' => $adminRole->id,
        ]);

        User::query()->updateOrCreate([
            'email' => 'cashier@coffee.test',
        ], [
            'name' => 'Cashier User',
            'password' => 'password123',
            'role_id' => $cashierRole->id,
        ]);

        $categoryData = [
            ['name' => 'Coffee', 'slug' => 'coffee', 'description' => 'Classic brewed and espresso drinks.'],
            ['name' => 'Non Coffee', 'slug' => 'non-coffee', 'description' => 'Chocolate and tea-based beverages.'],
            ['name' => 'Food', 'slug' => 'food', 'description' => 'Main dishes and brunch options.'],
            ['name' => 'Snack', 'slug' => 'snack', 'description' => 'Light bites and quick snacks.'],
            ['name' => 'Dessert', 'slug' => 'dessert', 'description' => 'Sweet menu items.'],
        ];

        $categoriesBySlug = collect($categoryData)
            ->mapWithKeys(function (array $item): array {
                $category = Category::query()->updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'is_active' => true,
                    ],
                );

                return [$item['slug'] => $category];
            });

        foreach ([
            ['name' => 'Cappuccino', 'slug' => 'cappuccino', 'price' => 4.98, 'category_slug' => 'coffee'],
            ['name' => 'Coffee Latte', 'slug' => 'coffee-latte', 'price' => 5.98, 'category_slug' => 'coffee'],
            ['name' => 'Americano', 'slug' => 'americano', 'price' => 5.50, 'category_slug' => 'coffee'],
            ['name' => 'V60', 'slug' => 'v60', 'price' => 5.98, 'category_slug' => 'coffee'],
        ] as $productData) {
            Product::query()->updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    'name' => $productData['name'],
                    'category_id' => $categoriesBySlug->get($productData['category_slug'])?->id,
                    'description' => 'Freshly brewed and perfect for customer orders.',
                    'price' => $productData['price'],
                    'is_active' => true,
                ],
            );
        }
    }
}
