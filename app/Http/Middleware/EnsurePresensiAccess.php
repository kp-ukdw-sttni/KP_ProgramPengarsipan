<?php

namespace App\Http\Middleware;

use App\Support\PresensiAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the attendance routes on PresensiAccess rather than a bare role list, so
 * every account posted under WK III Kemahasiswaan dan Alumni can fill in
 * attendance without needing a role assigned by hand.
 */
class EnsurePresensiAccess
{
    public function handle(Request $request, Closure $next, string $ability = 'access'): Response
    {
        $user = $request->user();

        $allowed = $ability === 'review'
            ? PresensiAccess::canReview($user)
            : PresensiAccess::canAccess($user);

        abort_unless($allowed, 403);

        return $next($request);
    }
}
