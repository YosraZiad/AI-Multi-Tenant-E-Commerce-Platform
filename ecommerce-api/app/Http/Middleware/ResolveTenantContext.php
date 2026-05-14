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
        $rawTenantId = $request->header('X-Tenant-Id', $request->query('tenant_id'));
        $tenantId = is_numeric($rawTenantId) ? (int) $rawTenantId : null;

        app(TenantContext::class)->setTenantId($tenantId > 0 ? $tenantId : null);

        return $next($request);
    }
}
