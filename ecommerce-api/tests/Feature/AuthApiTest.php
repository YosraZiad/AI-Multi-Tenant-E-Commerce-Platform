<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_owner_membership_and_returns_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'tenant_name' => 'Acme Store',
            'tenant_slug' => 'acme-store',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email'],
                'tenant' => ['id', 'name', 'slug'],
                'role',
            ]);

        $this->assertDatabaseHas('tenant_users', [
            'role' => 'owner',
        ]);
    }

    public function test_login_returns_tenant_scoped_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant->id, ['role' => 'admin']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
            'tenant_id' => $tenant->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('role', 'admin')
            ->assertJsonStructure([
                'access_token',
                'tenant' => ['id', 'name', 'slug'],
            ]);
    }

    public function test_protected_route_requires_sanctum_token(): void
    {
        $this->getJson('/api/v1/categories')->assertUnauthorized();
    }

    public function test_logout_invalidates_current_token(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant->id, ['role' => 'staff']);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'tenant_id' => $tenant->id,
        ])->assertOk();

        $token = (string) $loginResponse->json('access_token');
        [$tokenId] = explode('|', $token, 2);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => (int) $tokenId,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->withHeader('X-Tenant-Id', (string) $tenant->id)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => (int) $tokenId,
        ]);
    }
}
