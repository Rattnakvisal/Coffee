<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'admin.index')->name('index');

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
    });
