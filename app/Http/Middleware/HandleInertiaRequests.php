<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $route = Route::currentRouteName();
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'route' => $route,
            'flash' => [
                'message' => session('message'),
                'error' => session('error'),
            ],
            'notification' => session('notification'),
        ];
    }
}
