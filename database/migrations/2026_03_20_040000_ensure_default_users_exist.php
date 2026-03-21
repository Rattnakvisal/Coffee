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
        $cashierRoleId = DB::table('roles')->where('slug', 'cashier')->value('id');

        if (! $adminRoleId || ! $cashierRoleId) {
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
                [
                    'name' => 'System Cashier',
                    'first_name' => 'System',
                    'last_name' => 'Cashier',
                    'email' => 'cashier@coffee.test',
                    'phone' => '098765432',
                    'gender' => 'other',
                    'role_id' => $cashierRoleId,
                    'created_by' => null,
                    'password' => Hash::make('cashier12345'),
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

        $adminId = DB::table('users')->where('email', 'admin@coffee.test')->value('id');

        if ($adminId) {
            DB::table('users')
                ->where('email', 'cashier@coffee.test')
                ->update([
                    'created_by' => $adminId,
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep default users on rollback of this migration.
    }
};
