<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Support\TenantContext;

class CategoryPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->hasTenantAccess($user);
    }

    public function view(?User $user, Category $category): bool
    {
        return $user !== null && $this->tenantMatches($category->tenant_id);
    }

    public function create(?User $user): bool
    {
        return $this->hasTenantAccess($user);
    }

    public function update(?User $user, Category $category): bool
    {
        return $user !== null && $this->tenantMatches($category->tenant_id);
    }

    public function delete(?User $user, Category $category): bool
    {
        return $user !== null && $this->tenantMatches($category->tenant_id);
    }

    public function restore(?User $user, Category $category): bool
    {
        return $user !== null && $this->tenantMatches($category->tenant_id);
    }

    public function forceDelete(?User $user, Category $category): bool
    {
        return $user !== null && $this->tenantMatches($category->tenant_id);
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
