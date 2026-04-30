<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CarouselAdminController;
use App\Http\Controllers\CarouselController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('carrusel', [CarouselController::class, 'index'])->name('carrusel');
    Route::get('chat', [ChatController::class, 'index'])->name('chat');
    Route::post('chat', [ChatController::class, 'store'])->name('chat.store');

    Route::middleware('role:super')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::prefix('carrusel/admin')->name('carrusel.admin.')->group(function () {
        Route::get('/', [CarouselAdminController::class, 'index'])->name('index');
        Route::post('images', [CarouselAdminController::class, 'storeImage'])->name('images.store');
        Route::delete('images/{image}', [CarouselAdminController::class, 'destroyImage'])->name('images.destroy');
        Route::patch('settings', [CarouselAdminController::class, 'updateSettings'])->name('settings.update');
        Route::patch('reorder', [CarouselAdminController::class, 'reorder'])->name('reorder');
    });
});

require __DIR__.'/settings.php';
