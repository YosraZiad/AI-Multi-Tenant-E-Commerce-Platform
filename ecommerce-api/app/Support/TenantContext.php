<?php

namespace App\Support;

class TenantContext
{
    public function __construct(private ?int $tenantId = null)
    {
    }

    public function setTenantId(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function tenantId(): ?int
    {
        return $this->tenantId;
    }
}
