<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
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
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nik_nim' => $user->nik_nim,
                    'divisi_id' => $user->divisi_id,
                    'divisi' => $user->divisi?->name,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'notifications' => [
                'pendingPeminjamanCount' => fn () => $user && ($user->hasRole('Superadmin') || $user->hasRole('Admin') || $user->hasRole('Operator'))
                    ? \App\Models\Peminjaman::where('status_approval', 'Pending')->count()
                    : 0,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'ziggy' => fn () => class_exists(Ziggy::class)
                ? (new Ziggy())->toArray()
                : null,
        ];
    }
}
