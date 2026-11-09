<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');
        $token = str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : null;

        if (!$token) {
            return response()->json([
                'message' => 'Unauthenticated. Token tidak ditemukan.',
            ], 401);
        }

        $payload = AuthController::verifyToken($token);

        if (!$payload) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kadaluarsa. Silahkan login kembali.',
            ], 401);
        }

        // 1. Ambil model User berdasarkan UUID (sub) dari payload token
        $user = User::find($payload['sub']);
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan atau sudah tidak aktif.',
            ], 401);
        }

        // 2. Set user ke Laravel Auth Guard & Request User Resolver
        Auth::setUser($user);
        $request->setUserResolver(fn() => $user);

        // 3. Tetap simpan auth_payload untuk kompatibilitas jika ada controller yang pakai
        $request->attributes->set('auth_payload', $payload);

        return $next($request);
    }
}