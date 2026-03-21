<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $now = now();

        DB::table('roles')->upsert(
            [
                [
                    'name' => 'Administrator',
                    'slug' => 'admin',
                    'description' => 'Full access to admin dashboard and management.',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Cashier',
                    'slug' => 'cashier',
                    'description' => 'Access to cashier workspace and order flow.',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['slug'],
            ['name', 'description', 'is_active', 'updated_at'],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep roles data on rollback of this migration.
    }
};
