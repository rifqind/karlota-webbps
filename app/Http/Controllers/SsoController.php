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
            'realm'         => 'pegawai-bps',
            'clientId'      => config('services.sso.client_id'),
            'clientSecret'  => config('services.sso.client_secret'),
            'redirectUri'   => config('services.sso.redirect_uri'),
        ]);
    }
    public function ssoRedirect(Request $request)
    {
        // $provider = $this->getProvider();
        // $authUrl = $provider->getAuthorizationUrl();
        // $request->session()->put('oauth2state', $provider->getState());
        // $request->session()->save();
        // return redirect($authUrl);
        $request->session()->put('state', $state = Str::random(40));
        $query = http_build_query([
            'client_id' => config("services.sso.client_id"),
            'client_secret' => config("services.sso.client_secret"),
            // 'realm' => 'pegawai-bps',
            'scope' => 'profile-pegawai email',
            'redirect_uri' => config("services.sso.redirect_uri"),
            'response_type' => 'code',
            'state' => $state,
            'approval_prompt' => 'auto',
        ]);
        return redirect('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/auth?' . $query);
    }

    /**
     * Return the SSO authorization URL as JSON for Nuxt SPA.
     * Note: state parameter dikelola di sisi Nuxt via cookie.
     */
    public function getSsoUrl(Request $request): \Illuminate\Http\JsonResponse
    {
        $state = \Illuminate\Support\Str::random(40);

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

        // Kembalikan state juga agar Nuxt bisa simpan di cookie untuk verifikasi CSRF
        return response()->json([
            'url'   => $url,
            'state' => $state,
        ]);
    }

    public function ssoCallback(Request $request)
    {
        // Catatan: state validation dilakukan di sisi Nuxt via cookie (sso_state).
        // Web route ini tetap dipakai agar session tersedia untuk tukar code → token.

        $response = Http::asForm()->post('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.sso.client_id'),
            'client_secret' => config('services.sso.client_secret'),
            'redirect_uri'  => config('services.sso.redirect_uri'),
            'code'          => $request->code,
        ]);

        if ($response->failed()) {
            $nuxtUrl = config('services.nuxt_url', 'http://localhost:3000');
            return redirect($nuxtUrl . '/login?sso_error=token_exchange_failed');
        }

        $tokens      = $response->json();
        $accessToken = $tokens['access_token'] ?? null;

        if (!$accessToken) {
            $nuxtUrl = config('services.nuxt_url', 'http://localhost:3000');
            return redirect($nuxtUrl . '/login?sso_error=no_access_token');
        }

        // Ambil info user dari Keycloak
        $userInfoResponse = Http::withToken($accessToken)
            ->get('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/userinfo');

        if ($userInfoResponse->failed()) {
            $nuxtUrl = config('services.nuxt_url', 'http://localhost:3000');
            return redirect($nuxtUrl . '/login?sso_error=userinfo_failed');
        }

        $userInfo    = $userInfoResponse->json();
        $nipLama     = $userInfo['nip-lama'] ?? null;

        $current_user = $nipLama
            ? User::where('nip_lama', $nipLama)->first()
            : null;

        $nuxtUrl = config('services.nuxt_url', 'http://localhost:3000');

        if (!$current_user) {
            return redirect($nuxtUrl . '/login?sso_error=user_not_registered');
        }

        // Buat encrypted token (tidak butuh tabel personal_access_tokens)
        $encryptedToken = \App\Http\Controllers\Api\AuthController::generateToken($current_user);

        // Redirect ke halaman SSO callback Nuxt dengan token di URL
        return redirect($nuxtUrl . '/sso-callback?token=' . urlencode($encryptedToken));
    }

    public function getTokenAPI()
    {
        $client_id     = config('services.sso.client_id');
        $client_secret = config('services.sso.client_secret');
        $url_token     = 'https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/token';

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
