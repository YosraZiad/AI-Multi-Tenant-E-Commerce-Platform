<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_is_scoped_by_tenant_context(): void
    {
        $user = User::factory()->create();
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $user->tenants()->attach($tenantA->id, ['role' => 'owner', 'is_owner' => true]);

        Category::factory()->count(3)->forTenant($tenantA)->create();
        Category::factory()->count(2)->forTenant($tenantB)->create();

        $response = $this->actingAs($user)
            ->withHeaders(['X-Tenant-Id' => (string) $tenantA->id])
            ->getJson('/api/v1/categories');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $row) {
            $this->assertSame($tenantA->id, $row['tenant_id']);
        }
    }

    public function test_show_product_from_another_tenant_returns_not_found(): void
    {
        $user = User::factory()->create();
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $user->tenants()->attach($tenantA->id, ['role' => 'manager', 'is_owner' => false]);

        $categoryA = Category::factory()->forTenant($tenantA)->create();
        $categoryB = Category::factory()->forTenant($tenantB)->create();

        Product::factory()->forCategory($categoryA)->create();
        $otherTenantProduct = Product::factory()->forCategory($categoryB)->create();

        $this->actingAs($user)
            ->withHeaders(['X-Tenant-Id' => (string) $tenantA->id])
            ->getJson('/api/v1/products/'.$otherTenantProduct->id)
            ->assertNotFound();
    }
}
