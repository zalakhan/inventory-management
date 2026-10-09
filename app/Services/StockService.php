<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\ProductWarehouseStock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class StockService
{
    /**
     * Record stock IN and update the cached warehouse balance.
     */
    public function recordIn(
        int $warehouseId,
        int $productId,
        string|float $quantity,
        float|null $unitCost,
        Model $source,
        \DateTimeInterface $movementDate,
        User $user,
        ?string $notes = null,
    ): StockMovement {
        return $this->applyMovement(
            warehouseId: $warehouseId,
            productId: $productId,
            quantity: $quantity,
            direction: 'in',
            unitCost: $unitCost,
            source: $source,
            movementDate: $movementDate,
            user: $user,
            notes: $notes,
        );
    }

    /**
     * Record stock OUT and update the cached warehouse balance.
     *
     * @throws InsufficientStockException
     */
    public function recordOut(
        int $warehouseId,
        int $productId,
        string|float $quantity,
        float|null $unitCost,
        Model $source,
        \DateTimeInterface $movementDate,
        User $user,
        ?string $notes = null,
    ): StockMovement {
        return $this->applyMovement(
            warehouseId: $warehouseId,
            productId: $productId,
            quantity: $quantity,
            direction: 'out',
            unitCost: $unitCost,
            source: $source,
            movementDate: $movementDate,
            user: $user,
            notes: $notes,
        );
    }

    /**
     * Cancel a posted movement by writing an opposite reversal row.
     *
     * @throws InsufficientStockException
     */
    public function reverseMovement(StockMovement $movement, User $user, ?string $notes = null): StockMovement
    {
        if ($movement->status !== 'posted') {
            throw new InvalidArgumentException('Only posted movements can be reversed.');
        }

        $reversalDirection = $movement->direction === 'in' ? 'out' : 'in';

        $reversal = $this->applyMovement(
            warehouseId: $movement->warehouse_id,
            productId: $movement->product_id,
            quantity: $movement->quantity,
            direction: $reversalDirection,
            unitCost: $movement->unit_cost,
            source: $movement->source,
            movementDate: now(),
            user: $user,
            notes: $notes ?? 'Reversal of movement #'.$movement->id,
            reversalOfId: $movement->id,
        );

        $movement->update(['status' => 'cancelled']);

        return $reversal;
    }

    /**
     * Current cached quantity for a product in a warehouse.
     */
    public function getBalance(int $warehouseId, int $productId): string
    {
        $stock = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->value('quantity');

        return (string) ($stock ?? '0');
    }

    /**
     * Apply a stock movement and update product_warehouse_stock atomically.
     *
     * @throws InsufficientStockException
     */
    protected function applyMovement(
        int $warehouseId,
        int $productId,
        string|float $quantity,
        string $direction,
        float|null $unitCost,
        Model $source,
        \DateTimeInterface $movementDate,
        User $user,
        ?string $notes = null,
        ?int $reversalOfId = null,
    ): StockMovement {
        $quantity = bcadd((string) $quantity, '0', 4);

        if (bccomp($quantity, '0', 4) <= 0) {
            throw new InvalidArgumentException('Movement quantity must be greater than zero.');
        }

        $stockRow = ProductWarehouseStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (! $stockRow) {
            $stockRow = ProductWarehouseStock::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => '0',
            ]);

            $stockRow = ProductWarehouseStock::query()
                ->whereKey($stockRow->id)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $currentBalance = (string) $stockRow->quantity;

        if ($direction === 'out') {
            if (bccomp($currentBalance, $quantity, 4) < 0) {
                $product = $stockRow->product()->first(['name']);

                throw new InsufficientStockException(
                    productName: $product?->name ?? 'Product',
                    available: $currentBalance,
                    requested: $quantity,
                );
            }

            $newBalance = bcsub($currentBalance, $quantity, 4);
        } else {
            $newBalance = bcadd($currentBalance, $quantity, 4);
        }

        $movement = StockMovement::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'movement_date' => $movementDate,
            'direction' => $direction,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'balance_after' => $newBalance,
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
            'reversal_of_id' => $reversalOfId,
            'status' => 'posted',
            'notes' => $notes,
            'created_by' => $user->id,
        ]);

        $stockRow->update(['quantity' => $newBalance]);

        return $movement;
    }
}
