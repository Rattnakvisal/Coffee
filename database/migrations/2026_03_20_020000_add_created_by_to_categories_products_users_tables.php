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
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignId('created_by')
                ->nullable()
                ->after('is_active')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('created_by')
                ->nullable()
                ->after('is_active')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('created_by')
                ->nullable()
                ->after('role_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
