<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FenomenaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PdrbController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create']);
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
        Route::get('/hasil', [PdrbController::class, 'hasil'])
            ->name('hasil');
        Route::get('/diskrepansi', [PdrbController::class, 'diskrepansi'])
            ->name('diskrepansi');
        Route::get('/entri-fenomena', [FenomenaController::class, 'entri'])
            ->name('entri-fenomena');
    });
    Route::prefix('peng')->name('peng.')->group(function () {
        Route::get('/entri', [PdrbController::class, 'entri'])
            ->name('entri');
        Route::get('/adjustment', [PdrbController::class, 'adjustment'])
            ->name('adjustment');
        Route::get('/hasil', [PdrbController::class, 'hasil'])
            ->name('hasil');
        Route::get('/entri-fenomena', [FenomenaController::class, 'entri'])
            ->name('entri-fenomena');
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
    Route::get('/copy-hasil', [PdrbController::class, 'copyHasil'])
        ->name('pdrb.copy-hasil');

    //Adjustment
    Route::get('/get-adjustment', [PdrbController::class, 'getAdjustment'])
        ->name('pdrb.get-adjustment');
    Route::post('/save-adjustment', [PdrbController::class, 'saveAdjustment'])
        ->name('pdrb.save-adjustment');

    //Monitoring
    Route::get('/get-monitoring', [PdrbController::class, 'getMonitoring'])
        ->name('pdrb.get-monitoring');
    Route::get('/monitoring', [PdrbController::class, 'monitoring'])
        ->name('pdrb.monitoring');

    //Hasil
    Route::get('/get-hasil', [PdrbController::class, 'getHasil'])
        ->name('pdrb.get-hasil');

    //Diskrepansi
    Route::get('/get-diskrepansi', [PdrbController::class, 'getDiskrepansi'])
        ->name('pdrb.get-diskrepansi');

    //Fenomena
    Route::prefix('fenomena')->name('fenomena.')->group(function () {
        Route::get('/show', [FenomenaController::class, 'show'])
            ->name('show');
        Route::post('/save-fenomena', [FenomenaController::class, 'saveFenomena'])
            ->name('save-fenomena');
        Route::post('/submit-fenomena', [FenomenaController::class, 'submitFenomena'])
            ->name('submit-fenomena');
        Route::post('/unsubmit-fenomena', [FenomenaController::class, 'unsubmitFenomena'])
            ->name('unsubmit-fenomena');
        Route::get('/monitoring', [FenomenaController::class, 'monitoring'])
            ->name('monitoring');
        Route::get('/get-monitoring', [FenomenaController::class, 'getMonitoring'])
            ->name('get-monitoring');
    });
    //User
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/index', [UserController::class, 'index'])->name('index');
        Route::post('/store', [UserController::class, 'store'])
            ->name('store');
        Route::get('/fetch/{id}', [UserController::class, 'fetch'])
            ->name('fetch');
        Route::delete('/destroy/{id}', [UserController::class, 'destroy'])
            ->name('destroy');
    });
});

require __DIR__ . '/auth.php';
