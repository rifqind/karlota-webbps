<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FenomenaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PdrbController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\SpvController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\UserController;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->get('/', [AuthenticatedSessionController::class, 'create']);
Route::get('/token', function () {
    return csrf_token();
})->name('token');

//SSO
Route::get('/sso-login', [SsoController::class, 'ssoRedirect'])->name('sso-login');
Route::get('/sso-callback', [SsoController::class, 'ssoCallback'])->name('sso-callback');
Route::get('/sso-search', [SsoController::class, 'ssoAPI'])->name('sso-api');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])
        ->name('dashboard');
    Route::get('/index', [SummaryController::class, 'index'])->name('home.index');
    Route::get('/get-progress', [SummaryController::class, 'getProgress'])->name('home.get-progress');
    Route::post('/update-time', [HomeController::class, 'updateSummaryTime'])->name('home.update-time');
    Route::get('/home/get-summary', [HomeController::class, 'getSummary'])->name('home.get-summary');
    Route::get('/home/get-graph', [HomeController::class, 'getGraph'])->name('home.get-graph');

    Route::name('spv.')->group(function () {
        Route::prefix('lapus')->get('/spv', [SpvController::class, 'index'])->name('lapus');
        Route::prefix('peng')->get('/spv', [SpvController::class, 'index'])->name('peng');
    });
    Route::prefix('period')->name('period.')->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/index', [PeriodController::class, 'index'])
                ->name('index');
            Route::post('/store', [PeriodController::class, 'store'])
                ->name('store');
            Route::get('/fetch/{id}', [PeriodController::class, 'fetch'])
                ->name('fetch');
            Route::delete('/destroy/{id}', [PeriodController::class, 'destroy'])
                ->name('destroy');
        });

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
    Route::middleware(['role:admin|user'])->prefix('lapus')->name('lapus.')->group(function () {
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
    Route::middleware(['role:admin|user'])->prefix('peng')->name('peng.')->group(function () {
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

    //Entri
    Route::middleware(['role:admin|user'])->group(function () {
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
        Route::get('/watch-previous', [PdrbController::class, 'watchPrevious'])
            ->name('pdrb.watch-previous');
    });

    //Adjustment
    Route::middleware(['role:admin|user'])->get('/get-adjustment', [PdrbController::class, 'getAdjustment'])
        ->name('pdrb.get-adjustment');
    Route::middleware(['role:admin'])->post('/save-adjustment', [PdrbController::class, 'saveAdjustment'])
        ->name('pdrb.save-adjustment');

    //Monitoring
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/get-monitoring', [PdrbController::class, 'getMonitoring'])
            ->name('pdrb.get-monitoring');
        Route::get('/monitoring', [PdrbController::class, 'monitoring'])
            ->name('pdrb.monitoring');
    });

    Route::middleware(['role:admin|user'])->group(function () {
        //Hasil
        Route::get('/get-hasil', [PdrbController::class, 'getHasil'])
            ->name('pdrb.get-hasil');

        //Diskrepansi
        Route::get('/get-diskrepansi', [PdrbController::class, 'getDiskrepansi'])
            ->name('pdrb.get-diskrepansi');
    });

    //Fenomena
    Route::prefix('fenomena')->name('fenomena.')->group(function () {
        Route::middleware(['role:admin|user'])->group(function () {
            Route::get('/show', [FenomenaController::class, 'show'])
                ->name('show');
            Route::get('/index', [FenomenaController::class, 'index'])
                ->name('index');
            Route::get('/get-index', [FenomenaController::class, 'getIndex'])
                ->name('get-index');
            Route::post('/save-fenomena', [FenomenaController::class, 'saveFenomena'])
                ->name('save-fenomena');
            Route::post('/submit-fenomena', [FenomenaController::class, 'submitFenomena'])
                ->name('submit-fenomena');
            Route::post('/unsubmit-fenomena', [FenomenaController::class, 'unsubmitFenomena'])
                ->name('unsubmit-fenomena');
        });
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/monitoring', [FenomenaController::class, 'monitoring'])
                ->name('monitoring');
            Route::get('/get-monitoring', [FenomenaController::class, 'getMonitoring'])
                ->name('get-monitoring');
        });
    });
    //User
    Route::prefix('user')->name('user.')->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/index', [UserController::class, 'index'])->name('index');
            Route::post('/store', [UserController::class, 'store'])
                ->name('store');
            Route::get('/fetch/{id}', [UserController::class, 'fetch'])
                ->name('fetch');
            Route::delete('/destroy/{id}', [UserController::class, 'destroy'])
                ->name('destroy');
        });
        Route::middleware(['role:admin|user'])->group(function () {
            Route::get('/edit/{id}', [UserController::class, 'edit'])
                ->name('edit');
            Route::post('/edit', [UserController::class, 'edit']);
            Route::get('/question', [UserController::class, 'question'])->name('question');
            Route::post('/question', [UserController::class, 'question']);
            Route::delete('/delete-question/{id}', [UserController::class, 'question']);
            Route::get('/fetch-question/{id}', [UserController::class, 'fetchQuestion'])->name('fetch-question');
        });
    });
    //maintenance
    Route::post('/set-maintenance', function () {
        $maintenance = Maintenance::findOrFail(1);
        $maintenance->maintenance = !$maintenance->maintenance;
        $maintenance->save();
        return $maintenance->maintenance;
    });
    Route::get('/maintenance-status', function () {
        $maintenance = Maintenance::find(1);
        return response()->json([
            'maintenance' => $maintenance?->maintenance ?? false
        ]);
    });
});

require __DIR__ . '/auth.php';
