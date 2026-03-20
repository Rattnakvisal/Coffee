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
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_small_active')->default(true)->after('price_large');
            $table->boolean('is_medium_active')->default(true)->after('is_small_active');
            $table->boolean('is_large_active')->default(true)->after('is_medium_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['is_small_active', 'is_medium_active', 'is_large_active']);
        });
    }
};
