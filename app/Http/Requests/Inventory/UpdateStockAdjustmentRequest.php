<?php

namespace App\Http\Requests\Inventory;

class UpdateStockAdjustmentRequest extends StoreStockAdjustmentRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('stock_adjustments.create');
    }
}
