<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'userRoles' => $user?->getRoleNames() ?? [],
            'unreadNotificationsCount' => $user
                ? $user->appNotifications()->where('is_read', false)->count()
                : 0,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            // Midtrans client key is public-by-design (used to load Snap.js), never the server key.
            'midtrans' => [
                'clientKey' => config('services.midtrans.client_key'),
                'isProduction' => (bool) config('services.midtrans.is_production'),
            ],
        ];
    }
}
