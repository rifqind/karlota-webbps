<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;

class LocalAuthenticate extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Cek jika di lokal dan belum login
        // dd(app()->environment());
        if (app()->environment('production') && !Auth::check()) {
            Auth::loginUsingId('9e5d7684-c6e2-4b9d-a4f5-da8f412a69c9'); // Ganti dengan ID User target
            // Auth::loginUsingId(117); // Ganti dengan ID User target
        }

        // Jalankan fungsi handle bawaan (parent)
        return parent::handle($request, $next, ...$guards);
    }

    protected function redirectTo($request)
    {
        return $request->expectsJson() ? null : route('login');
    }
}
