<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\CustomerPayment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalePostingService
{
    public function __construct(
        private StockService $stockService,
        private CustomerLedgerService $ledgerService,
        private DocumentNumberService $documentNumberService,
    ) {}

    /**
     * Post a draft sale — deducts stock and writes customer ledger entries.
     *
     * @throws InvalidArgumentException|InsufficientStockException
     */
    public function post(Sale $sale, User $user): Sale
    {
        if ($sale->status !== 'draft') {
            throw new InvalidArgumentException('Only draft invoices can be posted.');
        }

        $sale->load(['items.product', 'customer']);

        if ($sale->items->isEmpty()) {
            throw new InvalidArgumentException('Cannot post an invoice with no line items.');
        }

        if (bccomp((string) $sale->amount_paid, (string) $sale->total_amount, 2) > 0) {
            throw new InvalidArgumentException('Amount paid cannot exceed invoice total.');
        }

        return DB::transaction(function () use ($sale, $user) {
            $this->ledgerService->ensureOpeningBalance($sale->customer, $user);

            foreach ($sale->items as $item) {
                $this->stockService->recordOut(
                    warehouseId: $sale->warehouse_id,
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    unitCost: (float) $item->product->purchase_rate,
                    source: $sale,
                    movementDate: $sale->sale_date,
                    user: $user,
                    notes: 'Sale invoice '.$sale->invoice_number,
                );
            }

            $this->ledgerService->recordDebit(
                customerId: $sale->customer_id,
                amount: $sale->total_amount,
                entryDate: $sale->sale_date,
                entryType: 'sale',
                reference: $sale,
                user: $user,
                description: 'Invoice '.$sale->invoice_number,
            );

            if (bccomp((string) $sale->amount_paid, '0', 2) > 0) {
                $receiptNumber = $this->documentNumberService->next(
                    'payments.receipt_prefix',
                    'payments.receipt_next',
                );

                $payment = CustomerPayment::create([
                    'receipt_number' => $receiptNumber,
                    'customer_id' => $sale->customer_id,
                    'payment_date' => $sale->sale_date,
                    'amount' => $sale->amount_paid,
                    'payment_method' => 'cash',
                    'notes' => 'Payment at invoice '.$sale->invoice_number,
                    'status' => 'posted',
                    'created_by' => $user->id,
                    'posted_by' => $user->id,
                    'posted_at' => now(),
                ]);

                $this->ledgerService->recordCredit(
                    customerId: $sale->customer_id,
                    amount: $sale->amount_paid,
                    entryDate: $sale->sale_date,
                    entryType: 'payment',
                    reference: $payment,
                    user: $user,
                    description: 'Payment '.$receiptNumber.' (at invoice '.$sale->invoice_number.')',
                );
            }

            $sale->update([
                'status' => 'posted',
                'posted_by' => $user->id,
                'posted_at' => now(),
            ]);

            return $sale->fresh([
                'items.product',
                'customer',
                'warehouse',
                'salesman',
                'area',
                'creator',
                'poster',
            ]);
        });
    }

    /**
     * Cancel a posted sale — reverses stock movements and ledger entries.
     *
     * @throws InvalidArgumentException|InsufficientStockException
     */
    public function cancel(Sale $sale, User $user, string $reason): Sale
    {
        if ($sale->status !== 'posted') {
            throw new InvalidArgumentException('Only posted invoices can be cancelled.');
        }

        return DB::transaction(function () use ($sale, $user, $reason) {
            $movements = $sale->stockMovements()
                ->where('status', 'posted')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($movements as $movement) {
                $this->stockService->reverseMovement(
                    movement: $movement,
                    user: $user,
                    notes: 'Cancellation of '.$sale->invoice_number.': '.$reason,
                );
            }

            $ledgerEntries = $sale->ledgerEntries()
                ->where('status', 'posted')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($ledgerEntries as $entry) {
                $this->ledgerService->reverseEntry(
                    entry: $entry,
                    user: $user,
                    description: 'Cancellation of '.$sale->invoice_number.': '.$reason,
                );
            }

            // Reverse any payment created at invoice posting time.
            CustomerPayment::query()
                ->where('customer_id', $sale->customer_id)
                ->where('status', 'posted')
                ->where('notes', 'Payment at invoice '.$sale->invoice_number)
                ->each(function (CustomerPayment $payment) use ($user, $sale, $reason) {
                    $payment->ledgerEntries()
                        ->where('status', 'posted')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                        ->each(fn ($entry) => $this->ledgerService->reverseEntry(
                            entry: $entry,
                            user: $user,
                            description: 'Cancellation of '.$sale->invoice_number.': '.$reason,
                        ));

                    $payment->update([
                        'status' => 'cancelled',
                        'cancelled_by' => $user->id,
                        'cancelled_at' => now(),
                    ]);
                });

            $sale->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $sale->fresh([
                'items.product',
                'customer',
                'warehouse',
                'salesman',
                'area',
                'creator',
                'canceller',
            ]);
        });
    }
}
