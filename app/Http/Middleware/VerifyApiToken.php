<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     * Validates the encrypted Bearer token issued by AuthController::generateToken().
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');
        $token = str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : null;

        if (! $token) {
            return response()->json([
                'message' => 'Unauthenticated. Token tidak ditemukan.',
            ], 401);
        }

        $payload = AuthController::verifyToken($token);

        if (! $payload) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kadaluarsa. Silahkan login kembali.',
            ], 401);
        }

        // Inject payload ke request agar controller bisa akses jika diperlukan
        $request->attributes->set('auth_payload', $payload);

        return $next($request);
    }
}
