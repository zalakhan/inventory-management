<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceivingItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stock_receiving_id',
        'product_id',
        'quantity',
        'purchase_rate',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'purchase_rate' => 'decimal:4',
            'line_total' => 'decimal:2',
        ];
    }

    public function stockReceiving(): BelongsTo
    {
        return $this->belongsTo(StockReceiving::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
