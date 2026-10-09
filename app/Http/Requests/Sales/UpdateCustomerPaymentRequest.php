<?php

namespace App\Http\Requests\Sales;

class UpdateCustomerPaymentRequest extends StoreCustomerPaymentRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payments.create');
    }
}
