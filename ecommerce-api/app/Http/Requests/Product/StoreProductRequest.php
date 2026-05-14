<?php

namespace App\Http\Requests\Product;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
                    ->where(fn ($query) => $query->where('tenant_id', (int) $this->input('tenant_id'))),
            ],
            'name' => ['required', 'string', 'max:180'],
            'slug' => [
                'required',
                'string',
                'max:220',
                Rule::unique('products', 'slug')
                    ->where(fn ($query) => $query->where('tenant_id', (int) $this->input('tenant_id'))),
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('tenant_id', (int) $this->input('tenant_id'))),
            ],
            'description' => ['nullable', 'string'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
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
