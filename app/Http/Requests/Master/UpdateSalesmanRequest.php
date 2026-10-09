<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalesmanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('salesmen.edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $salesman = $this->route('salesman');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('salesmen', 'code')->ignore($salesman)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'area_id' => ['nullable', 'integer', Rule::exists('areas', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['boolean'],
        ];
    }
}
