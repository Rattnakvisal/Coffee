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
        Schema::create('cashier_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->date('attended_on');
            $table->timestamp('checked_in_at');
            $table->timestamp('admin_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['cashier_id', 'attended_on']);
            $table->index(['attended_on', 'checked_in_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_attendances');
    }
};
