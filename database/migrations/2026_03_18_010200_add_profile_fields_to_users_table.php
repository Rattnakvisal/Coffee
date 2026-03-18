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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('gender', 20)->nullable()->after('phone');
            $table->string('avatar_path')->nullable()->after('gender');
        });

        $users = DB::table('users')->select('id', 'name')->get();

        foreach ($users as $user) {
            $fullName = trim((string) ($user->name ?? ''));

            if ($fullName === '') {
                continue;
            }

            $parts = preg_split('/\s+/', $fullName, 2) ?: [];
            $firstName = $parts[0] ?? null;
            $lastName = $parts[1] ?? null;

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'gender',
                'avatar_path',
            ]);
        });
    }
};
