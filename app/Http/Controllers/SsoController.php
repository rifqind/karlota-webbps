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

    public function ssoCallback(Request $request)
    {

        $state = $request->session()->pull('state');
        $codeVerifier = $request->session()->pull('code_verifier');
        // dd($state);
        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        $response = Http::asForm()->post('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config("services.sso.client_id"),
            'client_secret' => config("services.sso.client_secret"),
            'redirect_uri' => config("services.sso.redirect_uri"),
            'code_verifier' => $codeVerifier,
            'code' => $request->code,
        ]);
        $tokens = $response->json();
        // Store the tokens in the session or database
        session(['access_token' => $tokens['access_token']]);
        if (isset($tokens['refresh_token'])) {
            session(['refresh_token' => $tokens['refresh_token']]);
        }
        $accessToken = session('access_token');

        $userInfoResponse = Http::withToken($accessToken)->get('https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/userinfo');

        $userInfo = $userInfoResponse->json();
        $current_user = User::where('nip_lama', $userInfo['nip-lama'])->first();
        if ($current_user) {
            Auth::login($current_user);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard', absolute: false));
        }
        return redirect()->route('login')->with('error', 'Akun SSO mu belum terdaftar, tambahkan NIP lama di profilmu');
        // $provider = $this->getProvider();

        // // Cek state untuk mitigasi CSRF
        // $sessionState = $request->session()->get('oauth2state');
        // if (empty($request->state) || $request->state !== $sessionState) {
        //     $request->session()->forget('oauth2state');
        //     abort(403, 'Invalid state (CSRF detected)');
        // }

        // try {
        //     // Tukar authorization code dengan access token
        //     $token = $provider->getAccessToken('authorization_code', [
        //         'code' => $request->code,
        //     ]);
        // } catch (\Exception $e) {
        //     return response('Gagal mendapatkan akses token: '.$e->getMessage(), 500);
        // }

        // try {
        //     // Ambil data user dari Keycloak
        //     $user = $provider->getResourceOwner($token);

        //     return response()->json([
        //         'token' => $token->getToken(),
        //         'user'  => $user->toArray(),
        //     ]);
        // } catch (\Exception $e) {
        //     return response('Gagal mendapatkan data pengguna: '.$e->getMessage(), 500);
        // }
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
        $token = $this->getTokenAPI();
        $url_api = 'https://sso.bps.go.id/auth/realms/pegawai-bps/api-pegawai/username/' . $request->username;
        $response = Http::withToken($token)
            ->acceptJson()
            ->get($url_api);
        if ($response->failed()) {
            throw new \Exception('Gagal request API: ' . $response->body());
        }
        return $response->json();
    }
}
