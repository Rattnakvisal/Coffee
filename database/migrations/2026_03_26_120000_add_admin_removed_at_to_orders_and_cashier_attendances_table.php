<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'admin_removed_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->timestamp('admin_removed_at')->nullable()->after('admin_notified_at');
            });
        }

        if (Schema::hasTable('cashier_attendances') && !Schema::hasColumn('cashier_attendances', 'admin_removed_at')) {
            Schema::table('cashier_attendances', function (Blueprint $table): void {
                $table->timestamp('admin_removed_at')->nullable()->after('admin_notified_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'admin_removed_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('admin_removed_at');
            });
        }

        if (Schema::hasTable('cashier_attendances') && Schema::hasColumn('cashier_attendances', 'admin_removed_at')) {
            Schema::table('cashier_attendances', function (Blueprint $table): void {
                $table->dropColumn('admin_removed_at');
            });
        }
    }
};
