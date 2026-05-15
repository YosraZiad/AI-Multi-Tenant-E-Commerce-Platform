<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('sanctum') ?? $request->user();
        $tenantId = app(TenantContext::class)->tenantId();

        if ($user === null || $tenantId === null || empty($roles)) {
            abort(403, 'Tenant role authorization failed.');
        }

        if (! $user->hasTenantRole($tenantId, $roles)) {
            abort(403, 'Insufficient tenant role permissions.');
        }

        return $next($request);
    }
}
