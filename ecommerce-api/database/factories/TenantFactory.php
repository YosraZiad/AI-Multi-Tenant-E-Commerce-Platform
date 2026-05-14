<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();
        $slug = Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999);

        return [
            'name' => $name,
            'slug' => $slug,
            'domain' => $slug.'.saas.local',
            'status' => fake()->randomElement(['active', 'active', 'trial']),
            'settings' => [
                'currency' => fake()->randomElement(['USD', 'EUR', 'SAR']),
                'timezone' => fake()->timezone(),
            ],
        ];
    }
}
