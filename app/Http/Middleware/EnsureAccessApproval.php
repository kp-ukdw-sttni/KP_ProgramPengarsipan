<?php

namespace App\Http\Middleware;

use App\Support\AksesDokumen;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the access-approval queue on AksesDokumen rather than a bare role list,
 * so the sidebar and the route guard cannot disagree about who may decide.
 */
class EnsureAccessApproval
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(AksesDokumen::canApprove($request->user()), 403);

        return $next($request);
    }
}
