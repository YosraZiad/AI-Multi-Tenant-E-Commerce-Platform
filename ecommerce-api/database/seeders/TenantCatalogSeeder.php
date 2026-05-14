<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantCatalogSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Tenant::factory()
            ->count(3)
            ->create()
            ->each(function (Tenant $tenant): void {
                $categories = Category::factory()
                    ->count(5)
                    ->forTenant($tenant)
                    ->create();

                $categories->each(function (Category $category): void {
                    Product::factory()
                        ->count(15)
                        ->forCategory($category)
                        ->create();
                });
            });
    }
}
