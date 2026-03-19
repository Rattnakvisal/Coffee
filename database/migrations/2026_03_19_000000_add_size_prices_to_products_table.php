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
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('price_small', 10, 2)->nullable()->after('price');
            $table->decimal('price_medium', 10, 2)->nullable()->after('price_small');
            $table->decimal('price_large', 10, 2)->nullable()->after('price_medium');
        });

        DB::table('products')->update([
            'price_small' => DB::raw('price'),
            'price_medium' => DB::raw('price'),
            'price_large' => DB::raw('price'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['price_small', 'price_medium', 'price_large']);
        });
    }
};
