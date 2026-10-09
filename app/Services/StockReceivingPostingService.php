<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\StockReceiving;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockReceivingPostingService
{
    public function __construct(
        private StockService $stockService,
    ) {}

    /**
     * Post a draft stock receiving — writes IN movements and updates cached stock.
     *
     * @throws InvalidArgumentException|InsufficientStockException
     */
    public function post(StockReceiving $receiving, User $user): StockReceiving
    {
        if ($receiving->status !== 'draft') {
            throw new InvalidArgumentException('Only draft receipts can be posted.');
        }

        $receiving->load('items.product');

        if ($receiving->items->isEmpty()) {
            throw new InvalidArgumentException('Cannot post a receipt with no line items.');
        }

        return DB::transaction(function () use ($receiving, $user) {
            foreach ($receiving->items as $item) {
                $this->stockService->recordIn(
                    warehouseId: $receiving->warehouse_id,
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    unitCost: (float) $item->purchase_rate,
                    source: $receiving,
                    movementDate: $receiving->receiving_date,
                    user: $user,
                    notes: 'Stock receiving '.$receiving->receipt_number,
                );

                // Keep product default cost in sync with latest purchase rate.
                $item->product->update(['purchase_rate' => $item->purchase_rate]);
            }

            $receiving->update([
                'status' => 'posted',
                'posted_by' => $user->id,
                'posted_at' => now(),
            ]);

            return $receiving->fresh(['items.product', 'supplier', 'warehouse', 'creator', 'poster']);
        });
    }

    /**
     * Cancel a posted stock receiving — creates reversal OUT movements.
     *
     * @throws InvalidArgumentException|InsufficientStockException
     */
    public function cancel(StockReceiving $receiving, User $user, string $reason): StockReceiving
    {
        if ($receiving->status !== 'posted') {
            throw new InvalidArgumentException('Only posted receipts can be cancelled.');
        }

        return DB::transaction(function () use ($receiving, $user, $reason) {
            $movements = $receiving->stockMovements()
                ->where('status', 'posted')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($movements as $movement) {
                $this->stockService->reverseMovement(
                    movement: $movement,
                    user: $user,
                    notes: 'Cancellation of '.$receiving->receipt_number.': '.$reason,
                );
            }

            $receiving->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $receiving->fresh(['items.product', 'supplier', 'warehouse', 'creator', 'canceller']);
        });
    }
}
