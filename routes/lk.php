<?php

use App\Exports\IndeksHargaExport;
use App\Http\Controllers\Lk\IndeksHargaController;
use App\Http\Controllers\LK\KomoditasController;
use App\Http\Controllers\LK\LkController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('lk')->name('lk.')->group(function () {
        Route::get('/dashboard', [LkController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('/index', [LkController::class, 'index'])->name('index');
        Route::get('/get-data', [LkController::class, 'getData'])->name('getData');
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
        Route::post('/store', [IndeksHargaController::class, 'store'])->name('store');
        Route::get('/dasar', [IndeksHargaController::class, 'idxdasar'])->name('dasar.index');
        Route::get('/template', function () {
            return Excel::download(new IndeksHargaExport(), 'template-indeks-harga.xlsx');
        })->name('template');
        Route::get('/fetch/{label}/{tahun}', [IndeksHargaController::class, 'fetch'])->name('fetch');
        Route::patch('/update', [IndeksHargaController::class, 'update'])->name('update');
    });

    Route::get('/fetch-sector/{category_id}', [LkController::class, 'fetchSector'])->name('fetchSector');
    Route::get('/fetch-subsector/{sector_id}', [LkController::class, 'fetchSubsector'])->name('fetchSubsector');
});
