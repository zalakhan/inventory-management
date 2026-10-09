<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('categories.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:product_categories,code'],
            'parent_id' => ['nullable', 'integer', Rule::exists('product_categories', 'id')],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
