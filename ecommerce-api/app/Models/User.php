<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\TenantRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function belongsToTenant(int $tenantId): bool
    {
        return $this->tenants()->where('tenants.id', $tenantId)->exists();
    }

    public function currentTenantId(): ?int
    {
        $token = $this->currentAccessToken();

        if ($token !== null) {
            $abilities = $token->abilities ?? [];
            if (! is_array($abilities)) {
                $abilities = [];
            }

            foreach ($abilities as $ability) {
                if (str_starts_with($ability, 'tenant:')) {
                    $rawTenantId = substr($ability, 7);
                    if (is_numeric($rawTenantId)) {
                        return (int) $rawTenantId;
                    }
                }
            }
        }

        /** @var int|null $tenantId */
        $tenantId = $this->tenants()->value('tenants.id');

        return $tenantId;
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    public function hasTenantRole(int $tenantId, array $allowedRoles): bool
    {
        $role = $this->tenantRole($tenantId);

        return $role !== null && in_array($role, $allowedRoles, true);
    }

    public function tenantRole(int $tenantId): ?string
    {
        $role = $this->tenants()->where('tenants.id', $tenantId)->value('tenant_users.role');

        if (! is_string($role)) {
            return null;
        }

        return TenantRole::tryFrom($role)?->value;
    }
}
