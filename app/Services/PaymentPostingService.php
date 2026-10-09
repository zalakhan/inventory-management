<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\PaymentAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentPostingService
{
    public function __construct(
        private CustomerLedgerService $ledgerService,
    ) {}

    /**
     * Post a draft payment — writes a customer ledger credit entry.
     *
     * @param  array<int, array{sale_id: int, amount: string|float}>  $allocations
     *
     * @throws InvalidArgumentException
     */
    public function post(CustomerPayment $payment, User $user, array $allocations = []): CustomerPayment
    {
        if ($payment->status !== 'draft') {
            throw new InvalidArgumentException('Only draft payments can be posted.');
        }

        if (bccomp((string) $payment->amount, '0', 2) <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $payment->load('customer');

        return DB::transaction(function () use ($payment, $user, $allocations) {
            $this->ledgerService->ensureOpeningBalance($payment->customer, $user);

            $allocationTotal = '0';

            foreach ($allocations as $allocation) {
                $amount = bcadd((string) $allocation['amount'], '0', 2);
                $allocationTotal = bcadd($allocationTotal, $amount, 2);

                PaymentAllocation::create([
                    'customer_payment_id' => $payment->id,
                    'sale_id' => $allocation['sale_id'],
                    'amount' => $amount,
                ]);
            }

            if (bccomp($allocationTotal, (string) $payment->amount, 2) > 0) {
                throw new InvalidArgumentException('Allocated amounts cannot exceed payment total.');
            }

            $this->ledgerService->recordCredit(
                customerId: $payment->customer_id,
                amount: $payment->amount,
                entryDate: $payment->payment_date,
                entryType: 'payment',
                reference: $payment,
                user: $user,
                description: 'Payment '.$payment->receipt_number,
            );

            $payment->update([
                'status' => 'posted',
                'posted_by' => $user->id,
                'posted_at' => now(),
            ]);

            return $payment->fresh(['customer', 'allocations.sale', 'creator', 'poster']);
        });
    }

    /**
     * Cancel a posted payment — reverses ledger entries.
     *
     * @throws InvalidArgumentException
     */
    public function cancel(CustomerPayment $payment, User $user, string $reason): CustomerPayment
    {
        if ($payment->status !== 'posted') {
            throw new InvalidArgumentException('Only posted payments can be cancelled.');
        }

        return DB::transaction(function () use ($payment, $user, $reason) {
            $ledgerEntries = $payment->ledgerEntries()
                ->where('status', 'posted')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($ledgerEntries as $entry) {
                $this->ledgerService->reverseEntry(
                    entry: $entry,
                    user: $user,
                    description: 'Cancellation of '.$payment->receipt_number.': '.$reason,
                );
            }

            $payment->update([
                'status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $payment->fresh(['customer', 'allocations.sale', 'creator', 'canceller']);
        });
    }
}
