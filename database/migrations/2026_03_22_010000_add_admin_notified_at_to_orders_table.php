<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (! Schema::hasColumn('orders', 'admin_notified_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->timestamp('admin_notified_at')->nullable()->after('placed_at');
                $table->index('admin_notified_at');
            });
        }

        // Existing orders are considered already acknowledged by admin.
        DB::table('orders')
            ->whereNull('admin_notified_at')
            ->update([
                'admin_notified_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'admin_notified_at')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['admin_notified_at']);
            $table->dropColumn('admin_notified_at');
        });
    }
};
