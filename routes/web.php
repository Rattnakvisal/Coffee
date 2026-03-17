<?php

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$activeRoles = function (): Collection {
    if (! Schema::hasTable('roles')) {
        return new Collection();
    }

    return Role::query()->active()->orderBy('name')->get();
};

Route::get('/', function () use ($activeRoles) {
    return view('layouts.app', [
        'roles' => $activeRoles(),
    ]);
})->name('welcome');

Route::get('/login', function () use ($activeRoles) {
    $roles = $activeRoles();

    $defaultRole = $roles
        ->firstWhere('slug', 'admin')
        ?? $roles->firstWhere('slug', 'cashier')
        ?? $roles->first();

    abort_unless($defaultRole, 404, 'No active roles found.');

    return redirect()->route('login.form', ['role' => $defaultRole->slug]);
})->name('login');

Route::get('/login/{role}', function (string $role) use ($activeRoles) {
    $selectedRole = $activeRoles()->firstWhere('slug', $role);

    abort_unless($selectedRole, 404);

    return view('auth.login', [
        'selectedRole' => $selectedRole,
        'roles' => $activeRoles(),
    ]);
})->name('login.form');

Route::post('/login/{role}', function (Request $request, string $role) use ($activeRoles) {
    $selectedRole = $activeRoles()->firstWhere('slug', $role);

    abort_unless($selectedRole, 404);

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->onlyInput('email');
    }

    $request->session()->regenerate();

    $user = $request->user()->load('role');

    if (! $user->hasRole($selectedRole->slug)) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()
            ->withErrors([
                'email' => 'This account is not allowed to sign in as ' . strtolower($selectedRole->name) . '.',
            ])
            ->onlyInput('email');
    }

    $dashboardRoute = match ($selectedRole->slug) {
        'admin' => 'admin.index',
        'cashier' => 'cashier.index',
        default => 'welcome',
    };

    return redirect()->intended(route($dashboardRoute));
})->name('login.submit');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('welcome');
})->middleware('auth')->name('logout');

Route::view('/cashier', 'cashier.index')
    ->middleware(['auth', 'role:cashier'])
    ->name('cashier.index');

require __DIR__ . '/admin.php';
