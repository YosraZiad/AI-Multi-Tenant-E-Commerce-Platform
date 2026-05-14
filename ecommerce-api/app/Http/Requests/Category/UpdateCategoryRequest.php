<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        /** @var Category|null $category */
        $category = $this->route('category');

        return [
            'tenant_id' => ['required', 'integer', Rule::exists('tenants', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:160',
                Rule::unique('categories', 'slug')
                    ->ignore($category?->id)
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
