<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', (int) $this->input('tenant_id'))),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:220',
                Rule::unique('products', 'slug')
                    ->ignore($product?->id)
                    ->where(fn ($query) => $query->where('tenant_id', (int) $this->input('tenant_id'))),
            ],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->ignore($product?->id)
                    ->where(fn ($query) => $query->where('tenant_id', (int) $this->input('tenant_id'))),
            ],
            'description' => ['nullable', 'string'],
            'price_cents' => ['sometimes', 'required', 'integer', 'min:0'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'meta' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $tenantId = app(TenantContext::class)->tenantId();

        if ($tenantId !== null && ! $this->filled('tenant_id')) {
            $this->merge([
                'tenant_id' => $tenantId,
            ]);
        }
    }
}
