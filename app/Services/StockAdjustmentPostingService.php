<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockAdjustmentPostingService
{
    public function __construct(
        private StockService $stockService,
    ) {}

    /**
     * Post a draft stock adjustment — writes IN/OUT movements per line.
     *
     * @throws InvalidArgumentException|InsufficientStockException
     */
    public function post(StockAdjustment $adjustment, User $user): StockAdjustment
    {
        if ($adjustment->status !== 'draft') {
            throw new InvalidArgumentException('Only draft adjustments can be posted.');
        }

        $adjustment->load('items.product');

        if ($adjustment->items->isEmpty()) {
            throw new InvalidArgumentException('Cannot post an adjustment with no line items.');
        }

        return DB::transaction(function () use ($adjustment, $user) {
            foreach ($adjustment->items as $item) {
                $notes = 'Stock adjustment '.$adjustment->adjustment_number;
                if ($item->notes) {
                    $notes .= ': '.$item->notes;
                }

                if ($item->direction === 'in') {
                    $this->stockService->recordIn(
                        warehouseId: $adjustment->warehouse_id,
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        unitCost: (float) $item->product->purchase_rate,
                        source: $adjustment,
                        movementDate: $adjustment->adjustment_date,
                        user: $user,
                        notes: $notes,
                    );
                } else {
                    $this->stockService->recordOut(
                        warehouseId: $adjustment->warehouse_id,
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        unitCost: (float) $item->product->purchase_rate,
                        source: $adjustment,
                        movementDate: $adjustment->adjustment_date,
                        user: $user,
                        notes: $notes,
                    );
                }
            }

            $adjustment->update([
                'status' => 'posted',
                'posted_by' => $user->id,
                'posted_at' => now(),
            ]);

            return $adjustment->fresh(['items.product', 'warehouse', 'creator', 'poster']);
        });
    }

    /**
     * Cancel a posted stock adjustment — reverses all stock movements.
     *
     * @throws InvalidArgumentException|InsufficientStockException
     */
    public function cancel(StockAdjustment $adjustment, User $user, string $reason): StockAdjustment
    {
        if ($adjustment->status !== 'posted') {
            throw new InvalidArgumentException('Only posted adjustments can be cancelled.');
        }

        return DB::transaction(function () use ($adjustment, $user, $reason) {
            $movements = $adjustment->stockMovements()
                ->where('status', 'posted')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($movements as $movement) {
                $this->stockService->reverseMovement(
                    movement: $movement,
                    user: $user,
                    notes: 'Cancellation of '.$adjustment->adjustment_number.': '.$reason,
                );
            }

            $adjustment->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $adjustment->fresh(['items.product', 'warehouse', 'creator', 'canceller']);
        });
    }
}
