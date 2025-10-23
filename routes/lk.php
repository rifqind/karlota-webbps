<?php

use App\Http\Controllers\LK\KomoditasController;
use App\Http\Controllers\LK\LkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('lk')->name('lk.')->group(function () {
        Route::get('/dashboard', [LkController::class, 'dashboard'])
            ->name('dashboard');
    });
    Route::prefix('komoditas')->name('komoditas.')->group(function () {
        Route::get('/index', [KomoditasController::class, 'index'])->name('index');
    });
});
