<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'description' => fake()->optional()->paragraph(),
            'price_cents' => fake()->numberBetween(500, 150000),
            'currency' => fake()->randomElement(['USD', 'EUR', 'SAR']),
            'stock_quantity' => fake()->numberBetween(0, 500),
            'is_active' => fake()->boolean(90),
            'meta' => [
                'ai_tags' => fake()->words(3),
                'origin' => fake()->countryCode(),
            ],
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $category->tenant_id,
            'category_id' => $category->id,
        ]);
    }
}
