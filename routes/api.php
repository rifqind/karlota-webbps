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

// Public / Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/sso-url', [SsoController::class, 'getSsoUrl']);
});

// Period Fetch Routes for Nuxt SPA
Route::prefix('period')->group(function () {
    Route::get('/fetchYear', [\App\Http\Controllers\PeriodController::class, 'fetchYear']);
    Route::get('/fetchQuarter', [\App\Http\Controllers\PeriodController::class, 'fetchQuarter']);
    Route::get('/fetchPeriod', [\App\Http\Controllers\PeriodController::class, 'fetchPeriod']);
    Route::get('/fetchYearBefore', [\App\Http\Controllers\PeriodController::class, 'fetchYearBefore']);
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
Route::get('/subsectors', function (\Illuminate\Http\Request $request) {
    $type = $request->query('type', 'Lapangan Usaha');
    $subsectors = \App\Models\Subsector::where('type', $type)
        ->with(['sector.category'])
        ->get();
    return response()->json($subsectors);
});

// Protected — verifikasi encrypted token dari Authorization header
Route::get('/user', function (Request $request) {
    $authHeader = $request->header('Authorization', '');
    $token      = str_starts_with($authHeader, 'Bearer ')
        ? substr($authHeader, 7)
        : null;

    if (! $token) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $payload = AuthController::verifyToken($token);

    if (! $payload) {
        return response()->json(['message' => 'Token invalid atau expired'], 401);
    }

    return response()->json([
        'id'       => $payload['sub'],
        'name'     => $payload['name'],
        'email'    => $payload['email'],
        'role'     => $payload['role'],
        'nip_lama' => $payload['nip_lama'],
    ]);
});
