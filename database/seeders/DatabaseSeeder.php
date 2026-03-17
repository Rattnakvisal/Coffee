<?php

namespace Database\Seeders;

use App\Models\Role;
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
    }
}
