<?php

namespace App\Http\Middleware;

use App\Models\Peminjaman;
use App\Support\AksesDokumen;
use App\Support\PresensiAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                    'nidn_nip' => $user->nidn_nip,
                    'divisi_id' => $user->divisi_id,
                    // Shipped as an object, not a bare name: the sidebar needs the kode to decide
                    // whether this account sits inside the unit that owns attendance.
                    'divisi' => $user->divisi ? [
                        'id' => $user->divisi->id,
                        'name' => $user->divisi->name,
                        'kode' => $user->divisi->kode,
                    ] : null,
                    'can_presensi' => PresensiAccess::canAccess($user),
                    'can_review_presensi' => PresensiAccess::canReview($user),
                    'can_approve_access' => AksesDokumen::canApprove($user),
                    'roles' => $user->getRoleNames(),
                    // Wrapped in a closure so Inertia only runs this query when a
                    // page actually reads it. No current view does, so the
                    // permissions table stays out of the hot path.
                    'permissions' => fn () => $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'notifications' => [
                // Only the decision queue is badged now that the requester has no
                // page of their own, so the count is the whole pending queue and
                // it is only computed for someone who can actually act on it.
                'pendingPeminjamanCount' => fn () => $user && AksesDokumen::canApprove($user)
                    ? Peminjaman::where('status_approval', 'Pending')->count()
                    : 0,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'ziggy' => fn () => $this->ziggy(),
        ];
    }

    /**
     * Ziggy's route list, cached under a key derived from the live route table.
     *
     * A plain time-based key went stale whenever a route was added, and the
     * frontend then failed with an unhelpful "route not in the route list"
     * error until the cache expired. Folding a hash of the routes into the
     * cache key makes any route change invalidate it automatically.
     */
    protected function ziggy(): ?array
    {
        if (! class_exists(Ziggy::class)) {
            return null;
        }

        $fingerprint = collect(app('router')->getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getName().'|'.$route->uri().'|'.implode(',', $route->methods()))
            ->sort()
            ->implode("\n");

        return Cache::remember(
            'ziggy.routes.'.sha1($fingerprint),
            now()->addDay(),
            fn () => (new Ziggy)->toArray(),
        );
    }
}
