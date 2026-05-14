<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\TenantContext;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->hasTenantAccess($user);
    }

    public function view(?User $user, Product $product): bool
    {
        return $user !== null && $this->tenantMatches($product->tenant_id);
    }

    public function create(?User $user): bool
    {
        return $this->hasTenantAccess($user);
    }

    public function update(?User $user, Product $product): bool
    {
        return $user !== null && $this->tenantMatches($product->tenant_id);
    }

    public function delete(?User $user, Product $product): bool
    {
        return $user !== null && $this->tenantMatches($product->tenant_id);
    }

    public function restore(?User $user, Product $product): bool
    {
        return $user !== null && $this->tenantMatches($product->tenant_id);
    }

    public function forceDelete(?User $user, Product $product): bool
    {
        return $user !== null && $this->tenantMatches($product->tenant_id);
    }

    private function tenantMatches(int $resourceTenantId): bool
    {
        $tenantId = app(TenantContext::class)->tenantId();

        return $tenantId !== null && $tenantId === $resourceTenantId;
    }

    private function hasTenantAccess(?User $user): bool
    {
        $tenantId = app(TenantContext::class)->tenantId();

        return $user !== null && $tenantId !== null && $user->belongsToTenant($tenantId);
    }
}
