<?php

namespace App\Http\Controllers\Api;

use App\Enums\TenantRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $request->validated();

        /** @var array{user: User, tenant: Tenant, token: string, role: string} $result */
        $result = DB::transaction(function () use ($payload): array {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
            ]);

            $tenant = Tenant::create([
                'name' => $payload['tenant_name'],
                'slug' => $payload['tenant_slug'] ?? $this->generateTenantSlug($payload['tenant_name']),
                'domain' => $payload['domain'] ?? null,
                'status' => 'active',
                'settings' => [
                    'onboarding_completed' => false,
                ],
            ]);

            $user->tenants()->attach($tenant->id, [
                'role' => TenantRole::Owner->value,
            ]);

            $token = $user->createToken(
                'tenant-'.$tenant->id,
                ['tenant:'.$tenant->id, 'role:'.TenantRole::Owner->value]
            )->plainTextToken;

            return [
                'user' => $user,
                'tenant' => $tenant,
                'token' => $token,
                'role' => TenantRole::Owner->value,
            ];
        });

        return response()->json([
            'message' => 'Registration successful.',
            'token_type' => 'Bearer',
            'access_token' => $result['token'],
            'user' => $result['user'],
            'tenant' => $result['tenant'],
            'role' => $result['role'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $user = User::where('email', $payload['email'])->first();

        if ($user === null || ! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $tenantRelation = $user->tenants();
        if (! empty($payload['tenant_id'])) {
            $tenantRelation->where('tenants.id', (int) $payload['tenant_id']);
        } elseif (! empty($payload['tenant_slug'])) {
            $tenantRelation->where('tenants.slug', (string) $payload['tenant_slug']);
        }

        $tenant = $tenantRelation->first();
        if ($tenant === null) {
            if (empty($payload['tenant_id']) && empty($payload['tenant_slug'])) {
                $tenant = $user->tenants()->first();
            }
        }

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant_id' => ['No tenant access was found for this user.'],
            ]);
        }

        $role = (string) $tenant->pivot->role;
        $token = $user->createToken('tenant-'.$tenant->id, ['tenant:'.$tenant->id, 'role:'.$role])->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $user,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'role' => $role,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('sanctum')?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $request->user()->currentTenantId();
        $tenant = $tenantId ? $user->tenants()->where('tenants.id', $tenantId)->first() : null;

        return response()->json([
            'user' => $user,
            'tenant' => $tenant,
            'role' => $tenant?->pivot?->role,
        ]);
    }

    private function generateTenantSlug(string $tenantName): string
    {
        $base = Str::of($tenantName)->slug()->value();
        $slug = $base;
        $suffix = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }
}
