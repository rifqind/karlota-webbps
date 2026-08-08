<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Generate a signed, encrypted auth token berisi user data + expiry.
     * Tidak butuh tabel personal_access_tokens — pakai APP_KEY Laravel.
     */
    public static function generateToken(User $user): string
    {
        $payload = [
            'sub'      => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $user->role,
            'nip_lama' => $user->nip_lama,
            'exp'      => now()->addDay()->timestamp,
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Verify & decode token. Returns null jika invalid/expired.
     */
    public static function verifyToken(string $token): ?array
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);

            if (!$payload || $payload['exp'] < now()->timestamp) {
                return null;
            }

            return $payload;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Handle login request dari Nuxt SPA.
     */
    public function login(Request $request): JsonResponse
    {
        $loginKey = $request->input('username') ?? $request->input('name') ?? $request->input('email');

        if (!$loginKey || !$request->input('password')) {
            throw ValidationException::withMessages([
                'username' => ['Username dan password wajib diisi.'],
            ]);
        }

        $user = User::where('name', $loginKey)
            ->orWhere('email', $loginKey)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        $token = self::generateToken($user);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $user->role,
                'nip_lama' => $user->nip_lama,
            ],
        ]);
    }

    /**
     * Logout — token di client (cookie Nuxt) hanya perlu dihapus di sisi Nuxt.
     * Server-side tidak ada state yang perlu dihapus.
     */
    public function logout(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Berhasil logout']);
    }

    /**
     * Return authenticated user dari encrypted token di Authorization header.
     */
    public function me(Request $request): JsonResponse
    {
        $authHeader = $request->header('Authorization', '');
        $token      = str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : null;

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $payload = self::verifyToken($token);

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
    }
}
