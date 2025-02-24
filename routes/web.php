<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LapusController;
use App\Http\Controllers\PdrbController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/token', function () {
    return csrf_token();
})->name('token');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])
        ->name('dashboard');
    Route::prefix('period')->name('period.')->group(function () {
        Route::get('/index', [PeriodController::class, 'index'])
            ->name('index');
        Route::post('/store', [PeriodController::class, 'store'])
            ->name('store');
        Route::get('/fetch/{id}', [PeriodController::class, 'fetch'])
            ->name('fetch');
        Route::delete('/destroy/{id}', [PeriodController::class, 'destroy'])
            ->name('destroy');

        //fetching periods for rekons
        Route::get('/fetchYear', [PeriodController::class, 'fetchYear'])
            ->name('fetchYear');
        Route::get('/fetchQuarter', [PeriodController::class, 'fetchQuarter'])
            ->name('fetchQuarter');
        Route::get('/fetchPeriod', [PeriodController::class, 'fetchPeriod'])
            ->name('fetchPeriod');
        Route::get('/fetchYearBefore', [PeriodController::class, 'fetchYearBefore'])
            ->name('fetchYearBefore');
    });

    Route::prefix('lapus')->name('lapus.')->group(function () {
        Route::get('/entri', [PdrbController::class, 'entri'])
            ->name('entri');
        Route::get('/adjustment', [PdrbController::class, 'adjustment'])
            ->name('adjustment');
    });
    Route::prefix('peng')->name('peng.')->group(function () {
        Route::get('/entri', [PdrbController::class, 'entri'])
            ->name('entri');
        Route::get('/adjustment', [PdrbController::class, 'adjustment'])
            ->name('adjustment');
    });

    //Entri
    Route::get('/show-pdrb', [PdrbController::class, 'show'])
        ->name('pdrb.show');
    Route::post('/save-entri', [PdrbController::class, 'saveEntri'])
        ->name('pdrb.save-entri');
    Route::post('/submit-entri', [PdrbController::class, 'submitEntri'])
        ->name('pdrb.submit-entri');
    Route::post('/unsubmit-entri', [PdrbController::class, 'unsubmitEntri'])
        ->name('pdrb.unsubmit-entri');
    Route::get('/copy-entri', [PdrbController::class, 'copyEntri'])
        ->name('pdrb.copy-entri');

    //Adjustment
    Route::get('/get-adjustment', [PdrbController::class, 'getAdjustment'])
        ->name('pdrb.get-adjustment');
    Route::post('/save-adjustment', [PdrbController::class, 'saveAdjustment'])
        ->name('pdrb.save-adjustment');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
