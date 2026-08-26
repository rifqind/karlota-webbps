<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SsoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - untuk Nuxt SPA
| Menggunakan encrypted token via Crypt (tidak butuh personal_access_tokens)
|--------------------------------------------------------------------------
*/

// ─── Public Routes (tidak perlu token) ────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/sso-url', [SsoController::class, 'getSsoUrl']);
});

// ─── Protected Routes (wajib menyertakan Bearer token) ────────────────────────
Route::middleware('api.token')->group(function () {

    // Ambil profil user dari token
    Route::get('/user', function (Request $request) {
        $payload = $request->attributes->get('auth_payload');
        return response()->json([
            'id' => $payload['sub'],
            'name' => $payload['name'],
            'email' => $payload['email'],
            'role' => $payload['role'],
            'nip_lama' => $payload['nip_lama'],
        ]);
    });

    // Period Routes
    Route::prefix('period')->group(function () {
        Route::get('/index', [\App\Http\Controllers\PeriodController::class, 'index']);
        Route::post('/store', [\App\Http\Controllers\PeriodController::class, 'store']);
        Route::get('/fetch/{id}', [\App\Http\Controllers\PeriodController::class, 'fetch']);
        Route::delete('/destroy/{id}', [\App\Http\Controllers\PeriodController::class, 'destroy']);
        Route::get('/fetchYear', [\App\Http\Controllers\PeriodController::class, 'fetchYear']);
        Route::get('/fetchQuarter', [\App\Http\Controllers\PeriodController::class, 'fetchQuarter']);
        Route::get('/fetchPeriod', [\App\Http\Controllers\PeriodController::class, 'fetchPeriod']);
        Route::get('/fetchYearBefore', [\App\Http\Controllers\PeriodController::class, 'fetchYearBefore']);
    });

    // User Routes
    Route::prefix('user')->group(function () {
        Route::get('/index', [\App\Http\Controllers\Api\UserController::class, 'index']);
        Route::post('/store', [\App\Http\Controllers\Api\UserController::class, 'store']);
        Route::post('/profile', [\App\Http\Controllers\Api\UserController::class, 'updateProfile']);
        Route::get('/fetch/{id}', [\App\Http\Controllers\Api\UserController::class, 'fetch']);
        Route::delete('/destroy/{id}', [\App\Http\Controllers\Api\UserController::class, 'destroy']);
        Route::get('/satker', [\App\Http\Controllers\Api\UserController::class, 'satker']);
    });

    // SSO Search Routes for User Management
    Route::get('/sso-api', [\App\Http\Controllers\SsoController::class, 'ssoAPI']);
    Route::get('/sso-search', [\App\Http\Controllers\SsoController::class, 'ssoAPI']);

    // Produsen (Daftar Dinas) Routes
    Route::prefix('produsen')->group(function () {
        Route::get('/index', [\App\Http\Controllers\Api\ProdusenController::class, 'index']);
        Route::post('/store', [\App\Http\Controllers\Api\ProdusenController::class, 'store']);
        Route::get('/fetch/{id}', [\App\Http\Controllers\Api\ProdusenController::class, 'fetch']);
        Route::delete('/delete/{id}', [\App\Http\Controllers\Api\ProdusenController::class, 'destroy']);
        Route::get('/wilayah', [\App\Http\Controllers\Api\ProdusenController::class, 'wilayah']);
    });

    // Master Rows Routes
    Route::prefix('master/rows')->group(function () {
        Route::get('/index', [\App\Http\Controllers\Api\MasterRowController::class, 'index']);
        Route::post('/store', [\App\Http\Controllers\Api\MasterRowController::class, 'store']);
        Route::get('/fetch/{id}', [\App\Http\Controllers\Api\MasterRowController::class, 'fetch']);
        Route::delete('/delete/{id}', [\App\Http\Controllers\Api\MasterRowController::class, 'destroy']);
    });

    // Master Sekunder (Master Data) Routes
    Route::prefix('master/sekunder')->group(function () {
        Route::get('/index', [\App\Http\Controllers\Api\MasterSekunderController::class, 'index']);
        Route::get('/fetch/{id}', [\App\Http\Controllers\Api\MasterSekunderController::class, 'fetch']);
        Route::post('/update', [\App\Http\Controllers\Api\MasterSekunderController::class, 'update']);
        Route::delete('/delete/{id}', [\App\Http\Controllers\Api\MasterSekunderController::class, 'destroy']);
    });

    // Sekunder Data Routes (Daftar Data Sekunder)
    Route::prefix('sekunder')->group(function () {
        Route::get('/index', [\App\Http\Controllers\Api\SekunderDataController::class, 'index']);
        Route::get('/create-data', [\App\Http\Controllers\Api\SekunderDataController::class, 'createData']);
        Route::post('/store', [\App\Http\Controllers\Api\SekunderDataController::class, 'store']);
        Route::delete('/delete/{id}', [\App\Http\Controllers\Api\SekunderDataController::class, 'destroy']);
        Route::post('/add-year', [\App\Http\Controllers\Api\SekunderDataController::class, 'addYear']);
    });

    // Region Routes
    Route::get('/regions', function () {
        return response()->json(\App\Models\Region::select(['id as value', 'name as label'])->get());
    });

    // PDRB Data Routes
    Route::get('/show-pdrb', [\App\Http\Controllers\PdrbController::class, 'show']);
    Route::post('/save-entri', [\App\Http\Controllers\PdrbController::class, 'saveEntri']);
    Route::post('/submit-entri', [\App\Http\Controllers\PdrbController::class, 'submitEntri']);
    Route::post('/unsubmit-entri', [\App\Http\Controllers\PdrbController::class, 'unsubmitEntri']);
    Route::get('/copy-entri', [\App\Http\Controllers\PdrbController::class, 'copyEntri']);
    Route::get('/copy-hasil', [\App\Http\Controllers\PdrbController::class, 'copyHasil']);
    Route::get('/watch-previous', [\App\Http\Controllers\PdrbController::class, 'watchPrevious']);

    // Adjustment Routes
    Route::get('/get-adjustment', [\App\Http\Controllers\PdrbController::class, 'getAdjustment']);
    Route::post('/save-adjustment', [\App\Http\Controllers\PdrbController::class, 'saveAdjustment']);

    // Hasil Routes
    Route::get('/get-hasil', [\App\Http\Controllers\PdrbController::class, 'getHasil']);
    Route::get('/add-year-fetch', [\App\Http\Controllers\PdrbController::class, 'addYearFetch']);

    // Diskrepansi Routes
    Route::get('/get-diskrepansi', [\App\Http\Controllers\PdrbController::class, 'getDiskrepansi']);

    // Monitoring Route
    Route::get('/get-monitoring', [\App\Http\Controllers\PdrbController::class, 'getMonitoring']);

    // Dashboard & SPV Routes
    Route::get('/dashboard-data', [\App\Http\Controllers\HomeController::class, 'dashboardData']);
    Route::get('/home/get-summary', [\App\Http\Controllers\HomeController::class, 'getSummary']);
    Route::get('/home/get-graph', [\App\Http\Controllers\HomeController::class, 'getGraph']);
    Route::post('/home/build-summaries', [\App\Http\Controllers\HomeController::class, 'buildSummaries']);
    Route::get('/get-spv-data', [\App\Http\Controllers\SpvController::class, 'getSpvData']);

    Route::get('/subsectors', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\Subsector::with(['sector.category']);
        if ($request->has('type') && !empty($request->query('type'))) {
            $query->where('type', $request->query('type'));
        }
        return response()->json($query->get());
    });

    // Fenomena Routes
    Route::prefix('fenomena')->group(function () {
        Route::get('/show', [\App\Http\Controllers\FenomenaController::class, 'show']);
        Route::post('/save-fenomena', [\App\Http\Controllers\FenomenaController::class, 'saveFenomena']);
        Route::post('/submit-fenomena', [\App\Http\Controllers\FenomenaController::class, 'submitFenomena']);
        Route::post('/unsubmit-fenomena', [\App\Http\Controllers\FenomenaController::class, 'unsubmitFenomena']);
        Route::get('/get-monitoring', [\App\Http\Controllers\FenomenaController::class, 'getMonitoring']);
        Route::get('/get-index', [\App\Http\Controllers\FenomenaController::class, 'getIndex']);
    });
});

