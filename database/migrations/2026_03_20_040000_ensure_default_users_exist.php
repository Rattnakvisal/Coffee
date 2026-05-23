<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('users')) {
            return;
        }

        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');

        if (! $adminRoleId) {
            return;
        }

        $now = now();

        DB::table('users')->upsert(
            [
                [
                    'name' => 'System Admin',
                    'first_name' => 'System',
                    'last_name' => 'Admin',
                    'email' => 'admin@coffee.test',
                    'phone' => '012345678',
                    'gender' => 'other',
                    'role_id' => $adminRoleId,
                    'created_by' => null,
                    'password' => Hash::make('admin12345'),
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['email'],
            [
                'name',
                'first_name',
                'last_name',
                'phone',
                'gender',
                'role_id',
                'password',
                'email_verified_at',
                'updated_at',
            ],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep default users on rollback of this migration.
    }
};
