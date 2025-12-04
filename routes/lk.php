<?php

use App\Http\Controllers\Lk\IndeksHargaController;
use App\Http\Controllers\LK\KomoditasController;
use App\Http\Controllers\LK\LkController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('lk')->name('lk.')->group(function () {
        Route::get('/dashboard', [LkController::class, 'dashboard'])
            ->name('dashboard');
    });
    Route::prefix('komoditas')->name('komoditas.')->group(function () {
        Route::get('/index', [KomoditasController::class, 'index'])->name('index');
        Route::post('/store', [KomoditasController::class, 'store'])->name('store');
        Route::get('/download-template/komoditas', function () {
            $filePath = public_path('document/Template Komoditas.xlsx');
            return Response::download($filePath);
        });
        Route::get('/update/{id}', [KomoditasController::class, 'update']);
        Route::post('/update', [KomoditasController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [KomoditasController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('ih')->name('ih.')->group(function () {
        Route::get('/index', [IndeksHargaController::class, 'index'])->name('index');
        Route::get('/dasar', [IndeksHargaController::class, 'idxdasar'])->name('dasar.index');
    });
});
