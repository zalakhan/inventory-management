<?php

namespace App\Http\Requests\Sales;

class UpdateSaleReturnRequest extends StoreSaleReturnRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sale_returns.create');
    }
}
