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

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/sso-url', [SsoController::class, 'getSsoUrl']);
});

// Logout (tidak butuh auth server-side, client yang hapus cookie)
Route::post('/logout', [AuthController::class, 'logout']);

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
