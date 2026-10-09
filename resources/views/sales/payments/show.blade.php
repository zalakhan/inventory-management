<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $payment->receipt_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    @include('sales.payments._status', ['status' => $payment->status])
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($payment->status === 'draft')
                    @can('payments.post')
                        <form method="POST" action="{{ route('sales.payments.post', $payment) }}" onsubmit="return confirm('Post this payment? Customer ledger will be credited.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow">
                                Post Payment
                            </button>
                        </form>
                    @endcan
                    @can('payments.create')
                        <a href="{{ route('sales.payments.edit', $payment) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">Edit</a>
                        <form method="POST" action="{{ route('sales.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this draft payment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                        </form>
                    @endcan
                @endif

                @if ($payment->status === 'posted')
                    @can('payments.cancel')
                        <button type="button" x-data x-on:click="$dispatch('open-cancel-modal')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Cancel Payment
                        </button>
                    @endcan
                @endif

                <a href="{{ route('sales.payments.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Payment Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Customer</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $payment->customer?->name ?? '—' }}
                            @if ($payment->customer)
                                <a href="{{ route('sales.customer_ledger.show', $payment->customer) }}" class="ml-2 text-brand-eucalyptus hover:text-brand-moss-shadow text-xs">Ledger →</a>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Payment Date</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->payment_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Amount</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($payment->amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Method</dt>
                        <dd class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Reference No.</dt>
                        <dd class="font-medium text-gray-900">{{ $payment->reference_no ?: '—' }}</dd>
                    </div>
                    @if ($payment->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="text-gray-900">{{ $payment->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6 text-sm space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Audit Trail</h3>
                <div>
                    <span class="text-gray-500">Created by</span>
                    <p class="font-medium text-gray-900">{{ $payment->creator?->name ?? '—' }}</p>
                </div>
                @if ($payment->posted_at)
                    <div>
                        <span class="text-gray-500">Posted by</span>
                        <p class="font-medium text-gray-900">{{ $payment->poster?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->posted_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($payment->cancelled_at)
                    <div>
                        <span class="text-gray-500">Cancelled by</span>
                        <p class="font-medium text-gray-900">{{ $payment->canceller?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->cancelled_at->format('d M Y H:i') }}</p>
                        @if ($payment->cancellation_reason)
                            <p class="mt-1 text-gray-700">{{ $payment->cancellation_reason }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($payment->status === 'posted')
        @can('payments.cancel')
            <div x-data="{ open: {{ $errors->has('cancellation_reason') ? 'true' : 'false' }} }" x-on:open-cancel-modal.window="open = true">
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50" @click="open = false"></div>
                    <div x-show="open" x-transition class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Cancel Payment</h3>
                        <p class="mt-2 text-sm text-gray-600">This will reverse the customer ledger credit for this payment.</p>
                        <form method="POST" action="{{ route('sales.payments.cancel', $payment) }}" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="cancellation_reason" value="Reason" />
                                <textarea id="cancellation_reason" name="cancellation_reason" rows="3" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('cancellation_reason') }}</textarea>
                                <x-input-error :messages="$errors->get('cancellation_reason')" class="mt-1" />
                            </div>
                            <div class="flex gap-3 justify-end">
                                <button type="button" @click="open = false" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Close</button>
                                <button type="submit" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700">Confirm Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif
</x-app-layout>
