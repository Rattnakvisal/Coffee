<?php

use App\Http\Controllers\CashierController;
use App\Models\Role;
use App\Models\User;
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

Route::get('/', function (Request $request) use ($activeRoles) {
    $currentUser = $request->user();

    if ($currentUser !== null) {
        return redirect()->route($currentUser->hasRole('admin') ? 'admin.index' : 'cashier.index');
    }

    $cashierRole = $activeRoles()->firstWhere('slug', 'cashier');

    if ($cashierRole !== null) {
        $cashierUser = User::query()
            ->where('role_id', $cashierRole->id)
            ->orderBy('id')
            ->first();

        if ($cashierUser !== null) {
            Auth::login($cashierUser);
            $request->session()->regenerate();

            return redirect()->route('cashier.index');
        }
    }

    return redirect()->route('login.form', ['role' => 'admin']);
})->name('welcome');

Route::get('/login', function () use ($activeRoles) {
    $roles = $activeRoles()->whereIn('slug', ['admin'])->values();
    $defaultRole = $roles->firstWhere('slug', 'admin');

    abort_unless($defaultRole, 404, 'No active roles found.');

    return redirect()->route('login.form', ['role' => $defaultRole->slug]);
})->name('login');

Route::get('/login/{role}', function (string $role) use ($activeRoles) {
    abort_unless($role === 'admin', 404);
    $selectedRole = $activeRoles()->whereIn('slug', ['admin'])->firstWhere('slug', 'admin');

    abort_unless($selectedRole, 404);

    return view('auth.login', [
        'selectedRole' => $selectedRole,
        'roles' => collect([$selectedRole]),
    ]);
})->name('login.form');

Route::post('/login/{role}', function (Request $request, string $role) use ($activeRoles) {
    abort_unless($role === 'admin', 404);
    $selectedRole = $activeRoles()->whereIn('slug', ['admin'])->firstWhere('slug', 'admin');

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
        $actualRole = (string) ($user->role?->slug ?? '');
        $actualRoleName = strtolower((string) ($user->role?->name ?? 'this role'));

        if ($actualRole === 'cashier') {
            return redirect()
                ->route('cashier.index')
                ->with('status', 'Cashier account signed in. Use admin login to open the dashboard.');
        }

        if ($actualRole === 'admin') {
            return redirect()
                ->route('admin.index')
                ->with('status', 'Admin account signed in.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()
            ->withErrors([
                'email' => 'This account is not allowed to sign in as ' . $actualRoleName . '.',
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

Route::match(['GET', 'POST'], '/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:cashier'])
    ->prefix('cashier')
    ->name('cashier.')
    ->group(function (): void {
        Route::get('/', [CashierController::class, 'index'])->name('index');
        Route::get('/attendance', [CashierController::class, 'attendance'])->name('attendance');
        Route::get('/history', [CashierController::class, 'history'])->name('history');
        Route::post('/attendance/check', [CashierController::class, 'checkAttendance'])->name('attendance.check');
        Route::post('/dashboard', [CashierController::class, 'goToDashboard'])->name('dashboard.go');
        Route::post('/cart/add', [CashierController::class, 'addToCart'])->name('cart.add');
        Route::post('/cart/{itemKey}/increment', [CashierController::class, 'incrementCartItem'])->name('cart.increment');
        Route::post('/cart/{itemKey}/decrement', [CashierController::class, 'decrementCartItem'])->name('cart.decrement');
        Route::post('/order/place', [CashierController::class, 'placeOrder'])->name('order.place');
    });

require __DIR__ . '/admin.php';
