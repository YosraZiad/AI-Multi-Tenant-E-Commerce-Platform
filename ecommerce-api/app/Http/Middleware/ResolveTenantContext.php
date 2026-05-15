<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum') ?? $request->user();
        $tokenTenantId = null;

        if ($user !== null) {
            $accessToken = $user->currentAccessToken();

            if ($accessToken !== null) {
                $abilities = $accessToken->abilities ?? [];
                if (! is_array($abilities)) {
                    $abilities = [];
                }

                foreach ($abilities as $ability) {
                    if (str_starts_with($ability, 'tenant:')) {
                        $rawAbilityTenantId = substr($ability, 7);
                        if (is_numeric($rawAbilityTenantId)) {
                            $tokenTenantId = (int) $rawAbilityTenantId;
                        }
                        break;
                    }
                }
            }
        }

        $rawTenantId = $request->header('X-Tenant-Id', $request->query('tenant_id'));
        $headerTenantId = is_numeric($rawTenantId) ? (int) $rawTenantId : null;

        if ($user !== null && $headerTenantId !== null && ! $user->belongsToTenant($headerTenantId)) {
            abort(403, 'Requested tenant is not assigned to this user.');
        }

        if ($tokenTenantId !== null && $headerTenantId !== null && $tokenTenantId !== $headerTenantId) {
            abort(403, 'Token tenant does not match request tenant.');
        }

        $tenantId = $tokenTenantId ?? $headerTenantId;

        if ($tenantId === null && $user !== null) {
            $tenantId = $user->currentTenantId();
        }

        app(TenantContext::class)->setTenantId($tenantId > 0 ? $tenantId : null);

        return $next($request);
    }
}
