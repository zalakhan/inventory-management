<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CustomerLedgerService
{
    /**
     * Current outstanding balance for a customer (positive = customer owes us).
     */
    public function getBalance(int $customerId): string
    {
        $latestBalance = CustomerLedgerEntry::query()
            ->where('customer_id', $customerId)
            ->where('status', 'posted')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->value('balance_after');

        if ($latestBalance !== null) {
            return (string) $latestBalance;
        }

        $customer = Customer::query()->find($customerId);

        return (string) ($customer?->opening_balance ?? '0');
    }

    /**
     * Create an opening balance ledger row when the customer has a non-zero opening balance.
     */
    public function ensureOpeningBalance(Customer $customer, User $user): ?CustomerLedgerEntry
    {
        $openingBalance = bcadd((string) $customer->opening_balance, '0', 2);

        if (bccomp($openingBalance, '0', 2) === 0) {
            return null;
        }

        $hasOpeningEntry = CustomerLedgerEntry::query()
            ->where('customer_id', $customer->id)
            ->where('entry_type', 'opening')
            ->where('status', 'posted')
            ->exists();

        if ($hasOpeningEntry) {
            return null;
        }

        if (bccomp($openingBalance, '0', 2) > 0) {
            return $this->recordDebit(
                customerId: $customer->id,
                amount: $openingBalance,
                entryDate: now(),
                entryType: 'opening',
                reference: null,
                user: $user,
                description: 'Opening balance',
            );
        }

        return $this->recordCredit(
            customerId: $customer->id,
            amount: bcmul($openingBalance, '-1', 2),
            entryDate: now(),
            entryType: 'opening',
            reference: null,
            user: $user,
            description: 'Opening balance (credit)',
        );
    }

    /**
     * Record a debit — increases what the customer owes.
     */
    public function recordDebit(
        int $customerId,
        string|float $amount,
        \DateTimeInterface $entryDate,
        string $entryType,
        ?Model $reference,
        User $user,
        ?string $description = null,
        ?int $reversalOfId = null,
    ): CustomerLedgerEntry {
        return $this->applyEntry(
            customerId: $customerId,
            amount: $amount,
            direction: 'debit',
            entryDate: $entryDate,
            entryType: $entryType,
            reference: $reference,
            user: $user,
            description: $description,
            reversalOfId: $reversalOfId,
        );
    }

    /**
     * Record a credit — decreases what the customer owes.
     */
    public function recordCredit(
        int $customerId,
        string|float $amount,
        \DateTimeInterface $entryDate,
        string $entryType,
        ?Model $reference,
        User $user,
        ?string $description = null,
        ?int $reversalOfId = null,
    ): CustomerLedgerEntry {
        return $this->applyEntry(
            customerId: $customerId,
            amount: $amount,
            direction: 'credit',
            entryDate: $entryDate,
            entryType: $entryType,
            reference: $reference,
            user: $user,
            description: $description,
            reversalOfId: $reversalOfId,
        );
    }

    /**
     * Cancel a posted ledger entry by writing an opposite reversal row.
     */
    public function reverseEntry(CustomerLedgerEntry $entry, User $user, ?string $description = null): CustomerLedgerEntry
    {
        if ($entry->status !== 'posted') {
            throw new InvalidArgumentException('Only posted ledger entries can be reversed.');
        }

        $reversalDescription = $description ?? 'Reversal of ledger entry #'.$entry->id;

        if (bccomp((string) $entry->debit, '0', 2) > 0) {
            $reversal = $this->recordCredit(
                customerId: $entry->customer_id,
                amount: $entry->debit,
                entryDate: now(),
                entryType: $entry->entry_type,
                reference: $entry->reference,
                user: $user,
                description: $reversalDescription,
                reversalOfId: $entry->id,
            );
        } else {
            $reversal = $this->recordDebit(
                customerId: $entry->customer_id,
                amount: $entry->credit,
                entryDate: now(),
                entryType: $entry->entry_type,
                reference: $entry->reference,
                user: $user,
                description: $reversalDescription,
                reversalOfId: $entry->id,
            );
        }

        $entry->update(['status' => 'cancelled']);

        return $reversal;
    }

    /**
     * Apply a ledger entry and update the running balance atomically.
     */
    protected function applyEntry(
        int $customerId,
        string|float $amount,
        string $direction,
        \DateTimeInterface $entryDate,
        string $entryType,
        ?Model $reference,
        User $user,
        ?string $description = null,
        ?int $reversalOfId = null,
    ): CustomerLedgerEntry {
        $amount = bcadd((string) $amount, '0', 2);

        if (bccomp($amount, '0', 2) <= 0) {
            throw new InvalidArgumentException('Ledger amount must be greater than zero.');
        }

        $currentBalance = $this->getBalanceForUpdate($customerId);

        if ($direction === 'debit') {
            $newBalance = bcadd($currentBalance, $amount, 2);
            $debit = $amount;
            $credit = '0';
        } else {
            $newBalance = bcsub($currentBalance, $amount, 2);
            $debit = '0';
            $credit = $amount;
        }

        return CustomerLedgerEntry::create([
            'customer_id' => $customerId,
            'entry_date' => $entryDate,
            'entry_type' => $entryType,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'debit' => $debit,
            'credit' => $credit,
            'balance_after' => $newBalance,
            'description' => $description,
            'reversal_of_id' => $reversalOfId,
            'status' => 'posted',
            'created_by' => $user->id,
        ]);
    }

    /**
     * Locked balance read used while posting entries inside a transaction.
     */
    protected function getBalanceForUpdate(int $customerId): string
    {
        $latestEntry = CustomerLedgerEntry::query()
            ->where('customer_id', $customerId)
            ->where('status', 'posted')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($latestEntry) {
            return (string) $latestEntry->balance_after;
        }

        $customer = Customer::query()
            ->whereKey($customerId)
            ->lockForUpdate()
            ->firstOrFail();

        return (string) $customer->opening_balance;
    }
}
