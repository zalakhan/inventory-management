<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $saleReturn->return_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    @include('sales.returns._status', ['status' => $saleReturn->status])
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($saleReturn->status === 'draft')
                    @can('sale_returns.post')
                        <form method="POST" action="{{ route('sales.returns.post', $saleReturn) }}" onsubmit="return confirm('Post this return? Stock will be added and customer ledger credited.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow">
                                Post Return
                            </button>
                        </form>
                    @endcan
                    @can('sale_returns.create')
                        <a href="{{ route('sales.returns.edit', $saleReturn) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">Edit</a>
                        <form method="POST" action="{{ route('sales.returns.destroy', $saleReturn) }}" onsubmit="return confirm('Delete this draft return?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                        </form>
                    @endcan
                @endif

                @if ($saleReturn->status === 'posted')
                    @can('sale_returns.cancel')
                        <button type="button" x-data x-on:click="$dispatch('open-cancel-modal')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Cancel Return
                        </button>
                    @endcan
                @endif

                <a href="{{ route('sales.returns.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Return Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Customer</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $saleReturn->customer?->name ?? '—' }}
                            @if ($saleReturn->customer)
                                <a href="{{ route('sales.customer_ledger.show', $saleReturn->customer) }}" class="ml-2 text-brand-eucalyptus hover:text-brand-moss-shadow text-xs">Ledger →</a>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Warehouse</dt>
                        <dd class="font-medium text-gray-900">{{ $saleReturn->warehouse?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Return Date</dt>
                        <dd class="font-medium text-gray-900">{{ $saleReturn->return_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Original Invoice</dt>
                        <dd class="font-medium text-gray-900">
                            @if ($saleReturn->sale)
                                <a href="{{ route('sales.invoices.show', $saleReturn->sale) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">{{ $saleReturn->sale->invoice_number }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    @if ($saleReturn->reason)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Reason</dt>
                            <dd class="text-gray-900">{{ $saleReturn->reason }}</dd>
                        </div>
                    @endif
                    @if ($saleReturn->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="text-gray-900">{{ $saleReturn->notes }}</dd>
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
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($saleReturn->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product?->code }} — {{ $item->product?->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->quantity, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->rate, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700 text-right">Total Credit</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($saleReturn->total_amount, 2) }}</td>
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
                    <p class="font-medium text-gray-900">{{ $saleReturn->creator?->name ?? '—' }}</p>
                </div>
                @if ($saleReturn->posted_at)
                    <div>
                        <span class="text-gray-500">Posted by</span>
                        <p class="font-medium text-gray-900">{{ $saleReturn->poster?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $saleReturn->posted_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($saleReturn->cancelled_at)
                    <div>
                        <span class="text-gray-500">Cancelled by</span>
                        <p class="font-medium text-gray-900">{{ $saleReturn->canceller?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $saleReturn->cancelled_at->format('d M Y H:i') }}</p>
                        @if ($saleReturn->cancellation_reason)
                            <p class="mt-1 text-gray-700">{{ $saleReturn->cancellation_reason }}</p>
                        @endif
                    </div>
                @endif
            </div>

            @can('inventory.view')
                @if ($saleReturn->status !== 'draft')
                    <a href="{{ route('inventory.movements.index', ['source' => $saleReturn->return_number]) }}" class="block text-sm text-brand-eucalyptus hover:text-brand-moss-shadow">
                        View stock movements →
                    </a>
                @endif
            @endcan
        </div>
    </div>

    @if ($saleReturn->status === 'posted')
        @can('sale_returns.cancel')
            <div x-data="{ open: {{ $errors->has('cancellation_reason') ? 'true' : 'false' }} }" x-on:open-cancel-modal.window="open = true">
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50" @click="open = false"></div>
                    <div x-show="open" x-transition class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Cancel Return</h3>
                        <p class="mt-2 text-sm text-gray-600">This will reverse stock movements and customer ledger entries.</p>
                        <form method="POST" action="{{ route('sales.returns.cancel', $saleReturn) }}" class="mt-4 space-y-4">
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
