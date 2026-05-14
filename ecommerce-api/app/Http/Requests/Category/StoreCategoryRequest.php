<?php

namespace App\Http\Requests\Category;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:160',
                Rule::unique('categories', 'slug')
                    ->where(fn ($query) => $query->where('tenant_id', (int) $this->input('tenant_id'))),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
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
