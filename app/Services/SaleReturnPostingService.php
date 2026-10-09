<?php

namespace App\Services;

use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleReturnPostingService
{
    public function __construct(
        private StockService $stockService,
        private CustomerLedgerService $ledgerService,
    ) {}

    /**
     * Post a draft sale return — stock IN and customer ledger credit.
     *
     * @throws InvalidArgumentException
     */
    public function post(SaleReturn $saleReturn, User $user): SaleReturn
    {
        if ($saleReturn->status !== 'draft') {
            throw new InvalidArgumentException('Only draft returns can be posted.');
        }

        $saleReturn->load(['items.product', 'customer']);

        if ($saleReturn->items->isEmpty()) {
            throw new InvalidArgumentException('Cannot post a return with no line items.');
        }

        return DB::transaction(function () use ($saleReturn, $user) {
            $this->ledgerService->ensureOpeningBalance($saleReturn->customer, $user);

            foreach ($saleReturn->items as $item) {
                $this->stockService->recordIn(
                    warehouseId: $saleReturn->warehouse_id,
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    unitCost: (float) $item->product->purchase_rate,
                    source: $saleReturn,
                    movementDate: $saleReturn->return_date,
                    user: $user,
                    notes: 'Sale return '.$saleReturn->return_number,
                );
            }

            $this->ledgerService->recordCredit(
                customerId: $saleReturn->customer_id,
                amount: $saleReturn->total_amount,
                entryDate: $saleReturn->return_date,
                entryType: 'return',
                reference: $saleReturn,
                user: $user,
                description: 'Return '.$saleReturn->return_number,
            );

            $saleReturn->update([
                'status' => 'posted',
                'posted_by' => $user->id,
                'posted_at' => now(),
            ]);

            return $saleReturn->fresh([
                'items.product',
                'customer',
                'warehouse',
                'sale',
                'creator',
                'poster',
            ]);
        });
    }

    /**
     * Cancel a posted sale return — reverses stock and ledger entries.
     *
     * @throws InvalidArgumentException|\App\Exceptions\InsufficientStockException
     */
    public function cancel(SaleReturn $saleReturn, User $user, string $reason): SaleReturn
    {
        if ($saleReturn->status !== 'posted') {
            throw new InvalidArgumentException('Only posted returns can be cancelled.');
        }

        return DB::transaction(function () use ($saleReturn, $user, $reason) {
            $movements = $saleReturn->stockMovements()
                ->where('status', 'posted')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($movements as $movement) {
                $this->stockService->reverseMovement(
                    movement: $movement,
                    user: $user,
                    notes: 'Cancellation of '.$saleReturn->return_number.': '.$reason,
                );
            }

            $ledgerEntries = $saleReturn->ledgerEntries()
                ->where('status', 'posted')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($ledgerEntries as $entry) {
                $this->ledgerService->reverseEntry(
                    entry: $entry,
                    user: $user,
                    description: 'Cancellation of '.$saleReturn->return_number.': '.$reason,
                );
            }

            $saleReturn->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $saleReturn->fresh([
                'items.product',
                'customer',
                'warehouse',
                'sale',
                'creator',
                'canceller',
            ]);
        });
    }
}
