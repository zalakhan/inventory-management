<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('areas.edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $area = $this->route('area');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('areas', 'code')->ignore($area)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
