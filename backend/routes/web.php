<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('admin.login');
        Route::post('login', [AuthController::class, 'store'])->name('admin.login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('admin.logout');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('links', [LinkController::class, 'index'])->name('admin.links.index');
        Route::get('links/{link}/edit', [LinkController::class, 'edit'])->name('admin.links.edit');
        Route::patch('links/{link}', [LinkController::class, 'update'])->name('admin.links.update');
        Route::delete('links/{link}', [LinkController::class, 'destroy'])->name('admin.links.destroy');

        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('admin.tags.destroy');
    });
});
