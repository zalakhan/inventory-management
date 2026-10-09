<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $receiving->receipt_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    @include('inventory.stock_receiving._status', ['status' => $receiving->status])
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($receiving->status === 'draft')
                    @can('stock_receiving.post')
                        <form method="POST" action="{{ route('inventory.stock_receiving.post', $receiving) }}" onsubmit="return confirm('Post this receipt? Stock will be added to the warehouse.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow">
                                Post Receipt
                            </button>
                        </form>
                    @endcan
                    @can('stock_receiving.edit')
                        <a href="{{ route('inventory.stock_receiving.edit', $receiving) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('inventory.stock_receiving.destroy', $receiving) }}" onsubmit="return confirm('Delete this draft receipt?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    @endcan
                @endif

                @if ($receiving->status === 'posted')
                    @can('stock_receiving.cancel')
                        <button type="button" x-data x-on:click="$dispatch('open-cancel-modal')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Cancel Receipt
                        </button>
                    @endcan
                @endif

                <a href="{{ route('inventory.stock_receiving.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Receipt Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Supplier</dt>
                        <dd class="font-medium text-gray-900">{{ $receiving->supplier?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Warehouse</dt>
                        <dd class="font-medium text-gray-900">{{ $receiving->warehouse?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Receiving Date</dt>
                        <dd class="font-medium text-gray-900">{{ $receiving->receiving_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Supplier Reference</dt>
                        <dd class="font-medium text-gray-900">{{ $receiving->supplier_reference ?: '—' }}</dd>
                    </div>
                    @if ($receiving->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="text-gray-900">{{ $receiving->notes }}</dd>
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
                            @foreach ($receiving->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $item->product?->code }} — {{ $item->product?->name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->quantity, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->purchase_rate, 4) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-700 text-right">Subtotal</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($receiving->subtotal, 2) }}</td>
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
                    <p class="font-medium text-gray-900">{{ $receiving->creator?->name ?? '—' }}</p>
                </div>
                @if ($receiving->posted_at)
                    <div>
                        <span class="text-gray-500">Posted by</span>
                        <p class="font-medium text-gray-900">{{ $receiving->poster?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $receiving->posted_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
                @if ($receiving->cancelled_at)
                    <div>
                        <span class="text-gray-500">Cancelled by</span>
                        <p class="font-medium text-gray-900">{{ $receiving->canceller?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $receiving->cancelled_at->format('d M Y H:i') }}</p>
                        @if ($receiving->cancellation_reason)
                            <p class="mt-1 text-gray-700">{{ $receiving->cancellation_reason }}</p>
                        @endif
                    </div>
                @endif
            </div>

            @can('inventory.view')
                <a href="{{ route('inventory.movements.index', ['source' => $receiving->receipt_number]) }}" class="block text-sm text-brand-eucalyptus hover:text-brand-moss-shadow">
                    View stock movements →
                </a>
            @endcan
        </div>
    </div>

    {{-- Cancel modal --}}
    @if ($receiving->status === 'posted')
        @can('stock_receiving.cancel')
            <div x-data="{ open: {{ $errors->has('cancellation_reason') ? 'true' : 'false' }} }" x-on:open-cancel-modal.window="open = true">
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50" @click="open = false"></div>
                    <div x-show="open" x-transition class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Cancel Receipt</h3>
                        <p class="mt-2 text-sm text-gray-600">This will reverse all stock movements for this receipt.</p>
                        <form method="POST" action="{{ route('inventory.stock_receiving.cancel', $receiving) }}" class="mt-4 space-y-4">
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
