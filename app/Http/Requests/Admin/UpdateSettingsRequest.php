<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    /**
     * Flatten nested numbering groups into dotted setting keys.
     */
    protected function prepareForValidation(): void
    {
        $settings = $this->input('settings', []);
        $flat = [];

        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $flat["{$key}.{$subKey}"] = $subValue;
                }
            } else {
                $flat[$key] = $value;
            }
        }

        $this->merge(['settings' => $flat]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.company_name' => ['required', 'string', 'max:255'],
            'settings.company_phone' => ['nullable', 'string', 'max:50'],
            'settings.company_address' => ['nullable', 'string', 'max:1000'],
            'settings.sales\.invoice_prefix' => ['required', 'string', 'max:20'],
            'settings.sales\.invoice_next' => ['required', 'integer', 'min:1'],
            'settings.stock_receiving\.receipt_prefix' => ['required', 'string', 'max:20'],
            'settings.stock_receiving\.receipt_next' => ['required', 'integer', 'min:1'],
            'settings.payments\.receipt_prefix' => ['required', 'string', 'max:20'],
            'settings.payments\.receipt_next' => ['required', 'integer', 'min:1'],
            'settings.sale_returns\.return_prefix' => ['required', 'string', 'max:20'],
            'settings.sale_returns\.return_next' => ['required', 'integer', 'min:1'],
            'settings.stock_adjustments\.adjustment_prefix' => ['required', 'string', 'max:20'],
            'settings.stock_adjustments\.adjustment_next' => ['required', 'integer', 'min:1'],
        ];
    }
}
