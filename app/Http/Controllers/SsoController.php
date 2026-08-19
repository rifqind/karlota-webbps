<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route as FacadesRoute;
use Illuminate\Support\Str;
use Inertia\Inertia;
use InvalidArgumentException;
use JKD\SSO\Client\Provider\Keycloak;

class SsoController extends Controller
{
    //
    protected function getProvider()
    {
        return new Keycloak([
            'authServerUrl' => 'https://sso.bps.go.id',
            'realm' => 'pegawai-bps',
            'clientId' => config('services.sso.client_id'),
            'clientSecret' => config('services.sso.client_secret'),
            'redirectUri' => config('services.sso.redirect_uri'),
        ]);
    }
    public function ssoRedirect(Request $request)
    {
        $from = $request->query('from') ?? $request->query('source');
        $ssoSource = ($from === 'nuxt' || $from === 'v2') ? 'nuxt' : 'web';
        $state = ($ssoSource === 'nuxt' ? 'nuxt_' : '') . Str::random(40);

        $request->session()->put('state', $state);
        $request->session()->put('sso_source', $ssoSource);

        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'client_secret' => config('services.sso.client_secret'),
            'scope' => 'profile-pegawai email',
            'redirect_uri' => config('services.sso.redirect_uri'),
            'response_type' => 'code',
            'state' => $state,
            'approval_prompt' => 'auto',
        ]);
        return redirect('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/auth?' . $query);
    }

    /**
     * Return the SSO authorization URL as JSON for Nuxt SPA.
     * Note: state parameter dikelola di sisi Nuxt via cookie & session backend.
     */
    public function getSsoUrl(Request $request): \Illuminate\Http\JsonResponse
    {
        $state = 'nuxt_' . Str::random(40);
        // Tidak pakai session karena endpoint ini dipanggil dari API route (stateless).
        // Nuxt harus menyimpan state di cookie/localStorage-nya sendiri untuk verifikasi CSRF.

        $query = http_build_query([
            'client_id'       => config('services.sso.client_id'),
            'client_secret'   => config('services.sso.client_secret'),
            'scope'           => 'profile-pegawai email',
            'redirect_uri'    => config('services.sso.redirect_uri'),
            'response_type'   => 'code',
            'state'           => $state,
            'approval_prompt' => 'auto',
        ]);

        $url = 'https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/auth?' . $query;
        // Kembalikan state agar Nuxt simpan di cookie untuk verifikasi CSRF saat callback
        return response()->json([
            'url'   => $url,
            'state' => $state,
        ]);
    }

    public function ssoCallback(Request $request)
    {
        $sessionState = $request->session()->pull('state');
        $ssoSource = $request->session()->pull('sso_source');

        // Deteksi apakah request SSO berasal dari Nuxt (v2) atau Karlota Biasa (Web)
        $isNuxt = ($ssoSource === 'nuxt') || Str::startsWith($request->state ?? '', 'nuxt_');

        $nuxtUrl = config('services.nuxt_url', 'http://localhost:8000/v2');

        $response = Http::asForm()->post('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.sso.client_id'),
            'client_secret' => config('services.sso.client_secret'),
            'redirect_uri' => config('services.sso.redirect_uri'),
            'code' => $request->code,
        ]);

        if ($response->failed()) {
            if ($isNuxt) {
                return redirect($nuxtUrl . '/login?sso_error=token_exchange_failed');
            }
            return redirect()->route('login')->with('error', 'Gagal melakukan login SSO (token exchange failed).');
        }

        $tokens = $response->json();
        $accessToken = $tokens['access_token'] ?? null;

        if (!$accessToken) {
            if ($isNuxt) {
                return redirect($nuxtUrl . '/login?sso_error=no_access_token');
            }
            return redirect()->route('login')->with('error', 'Gagal melakukan login SSO (no access token).');
        }

        // Simpan token di session jika dari Karlota biasa
        if (!$isNuxt) {
            session(['access_token' => $accessToken]);
            if (isset($tokens['refresh_token'])) {
                session(['refresh_token' => $tokens['refresh_token']]);
            }
        }

        // Ambil info user dari Keycloak
        $userInfoResponse = Http::withToken($accessToken)
            ->get('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/userinfo');

        if ($userInfoResponse->failed()) {
            if ($isNuxt) {
                return redirect($nuxtUrl . '/login?sso_error=userinfo_failed');
            }
            return redirect()->route('login')->with('error', 'Gagal mengambil data user dari SSO.');
        }

        $userInfo = $userInfoResponse->json();
        $nipLama = $userInfo['nip-lama'] ?? null;

        $current_user = $nipLama
            ? User::where('nip_lama', $nipLama)->first()
            : null;

        if (!$current_user) {
            if ($isNuxt) {
                return redirect($nuxtUrl . '/login?sso_error=user_not_registered');
            }
            return redirect()->route('login')->with('error', 'Akun SSO mu belum terdaftar, tambahkan NIP lama di profilmu');
        }

        if ($isNuxt) {
            // Flow untuk Nuxt v2: Buat encrypted token & redirect ke Nuxt sso-callback
            $encryptedToken = \App\Http\Controllers\Api\AuthController::generateToken($current_user);
            return redirect($nuxtUrl . '/sso-callback?token=' . urlencode($encryptedToken));
        }

        // Flow untuk Karlota Biasa (Web session): Login user dan redirect ke dashboard
        Auth::login($current_user);
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function getTokenAPI()
    {
        $client_id = config('services.sso.client_id');
        $client_secret = config('services.sso.client_secret');
        $url_token = 'https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/token';

        $response = Http::asForm()
            ->withBasicAuth($client_id, $client_secret)
            ->post($url_token, ['grant_type' => 'client_credentials']);
        if ($response->failed()) {
            throw new \Exception('Gagal mendapatkan access token: ' . $response->body());
        }
        $json = $response->json();
        return $json['access_token'] ?? null;
    }

    public function ssoAPI(Request $request)
    {
        try {
            $username = $request->query('username') ?? $request->username;
            if (!$username) {
                return response()->json(['error' => 'Username SSO wajib diisi.'], 422);
            }
            $token = $this->getTokenAPI();
            $url_api = 'https://sso.bps.go.id/auth/realms/pegawai-bps/api-pegawai/username/' . $username;
            $response = Http::withToken($token)
                ->acceptJson()
                ->get($url_api);

            if ($response->failed()) {
                return response()->json([], 404);
            }

            return response()->json($response->json());
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
