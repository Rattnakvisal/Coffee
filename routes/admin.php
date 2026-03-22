<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications.index');
        Route::post('/notifications/mark-read', [DashboardController::class, 'markNotificationsRead'])->name('notifications.read');
        Route::get('/search', [DashboardController::class, 'search'])->name('search');
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/transactions', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/reports', [ReportsController::class, 'reports'])->name('reports');
        Route::get('/reports/export/excel', [ReportsController::class, 'exportReportsExcel'])->name('reports.export.excel');
        Route::get('/reports/export/pdf', [ReportsController::class, 'exportReportsPdf'])->name('reports.export.pdf');

        Route::prefix('users')
            ->name('users.')
            ->controller(UserController::class)
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/suggestions', 'suggestions')->name('suggestions');
                Route::post('/', 'store')->name('store');
                Route::get('/{user}/edit', 'edit')->name('edit');
                Route::put('/{user}', 'update')->name('update');
                Route::delete('/{user}', 'destroy')->name('destroy');
            });

        Route::prefix('products')
            ->name('products.')
            ->controller(ProductController::class)
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::put('/{product}', 'update')->name('update');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });

        Route::prefix('categories')
            ->name('categories.')
            ->controller(CategoryController::class)
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::put('/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });

        Route::prefix('settings')
            ->name('settings.')
            ->controller(SettingController::class)
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::put('/profile', 'updateProfile')->name('profile.update');
                Route::put('/password', 'updatePassword')->name('password.update');
            });
    });
