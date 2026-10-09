<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $sale->invoice_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    @include('sales.invoices._status', ['status' => $sale->status])
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($sale->status === 'draft')
                    @can('sales.post')
                        <form method="POST" action="{{ route('sales.invoices.post', $sale) }}" onsubmit="return confirm('Post this invoice? Stock will be deducted and customer ledger will be updated.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow">
                                Post Invoice
                            </button>
                        </form>
                    @endcan
                    @can('sales.edit')
                        <a href="{{ route('sales.invoices.edit', $sale) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('sales.invoices.destroy', $sale) }}" onsubmit="return confirm('Delete this draft invoice?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    @endcan
                @endif

                @if ($sale->status === 'posted')
                    @can('sales.cancel')
                        <button type="button" x-data x-on:click="$dispatch('open-cancel-modal')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Cancel Invoice
                        </button>
                    @endcan
                @endif

                <a href="{{ route('sales.invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Invoice Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Customer</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $sale->customer?->name ?? '—' }}
                            @if ($sale->customer)
                                <a href="{{ route('sales.customer_ledger.show', $sale->customer) }}" class="ml-2 text-brand-eucalyptus hover:text-brand-moss-shadow text-xs">Ledger →</a>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Warehouse</dt>
                        <dd class="font-medium text-gray-900">{{ $sale->warehouse?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Sale Date</dt>
                        <dd class="font-medium text-gray-900">{{ $sale->sale_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Salesman</dt>
                        <dd class="font-medium text-gray-900">{{ $sale->salesman?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Area</dt>
                        <dd class="font-medium text-gray-900">{{ $sale->area?->name ?? '—' }}</dd>
                    </div>
                    @if ($sale->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="text-gray-900">{{ $sale->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Line Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rate</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Disc.</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $item->product?->code }} — {{ $item->product?->name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->quantity, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->rate, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->discount_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-700 text-right">Subtotal</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($sale->subtotal, 2) }}</td>
                            </tr>
                            @if ($sale->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-700 text-right">Invoice Discount</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">−{{ number_format($sale->discount_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 text-right">Total</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                            @if ($sale->amount_paid > 0)
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-700 text-right">Amount Paid</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($sale->amount_paid, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 text-right">Outstanding</td>
                                <td class="px-4 py-3 text-sm font-semibold text-brand-eucalyptus text-right">{{ number_format($sale->outstanding_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6 text-sm space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Audit Trail</h3>
                <div>
                    <span class="text-gray-500">Created by</span>
                    <p class="font-medium text-gray-900">{{ $sale->creator?->name ?? '—' }}</p>
                </div>
                @if ($sale->posted_at)
                    <div>
                        <span class="text-gray-500">Posted by</span>
                        <p class="font-medium text-gray-900">{{ $sale->poster?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $sale->posted_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($sale->cancelled_at)
                    <div>
                        <span class="text-gray-500">Cancelled by</span>
                        <p class="font-medium text-gray-900">{{ $sale->canceller?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $sale->cancelled_at->format('d M Y H:i') }}</p>
                        @if ($sale->cancellation_reason)
                            <p class="mt-1 text-gray-700">{{ $sale->cancellation_reason }}</p>
                        @endif
                    </div>
                @endif
            </div>

            @can('inventory.view')
                @if ($sale->status !== 'draft')
                    <a href="{{ route('inventory.movements.index', ['source' => $sale->invoice_number]) }}" class="block text-sm text-brand-eucalyptus hover:text-brand-moss-shadow">
                        View stock movements →
                    </a>
                @endif
            @endcan
        </div>
    </div>

    @if ($sale->status === 'posted')
        @can('sales.cancel')
            <div x-data="{ open: {{ $errors->has('cancellation_reason') ? 'true' : 'false' }} }" x-on:open-cancel-modal.window="open = true">
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50" @click="open = false"></div>
                    <div x-show="open" x-transition class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Cancel Invoice</h3>
                        <p class="mt-2 text-sm text-gray-600">This will reverse stock movements and customer ledger entries for this invoice.</p>
                        <form method="POST" action="{{ route('sales.invoices.cancel', $sale) }}" class="mt-4 space-y-4">
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
