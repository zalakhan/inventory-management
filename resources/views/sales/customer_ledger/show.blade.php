<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $customer->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Customer ledger statement — Current outstanding:
                    <span class="font-semibold {{ bccomp($currentBalance, '0', 2) > 0 ? 'text-red-600' : 'text-brand-eucalyptus' }}">
                        {{ number_format($currentBalance, 2) }}
                    </span>
                </p>
            </div>
            <a href="{{ route('sales.customer_ledger.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50 shrink-0">
                Back to Customers
            </a>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="date" name="from_date" value="{{ request('from_date') }}"
                class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <input type="date" name="to_date" value="{{ request('to_date') }}"
                class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <select name="entry_type" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All types</option>
                @foreach (['opening', 'sale', 'payment', 'return', 'adjustment'] as $type)
                    <option value="{{ $type }}" @selected(request('entry_type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
            <span class="font-medium text-gray-900">{{ $customer->code }}</span>
            @if ($customer->phone)
                · {{ $customer->phone }}
            @endif
            @if ($customer->area)
                · {{ $customer->area->name }}
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($entries as $entry)
                        <tr class="{{ $entry->status === 'cancelled' ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $entry->entry_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($entry->entry_type) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $entry->description ?? '—' }}
                                @if ($entry->reference_type === \App\Models\Sale::class && $entry->reference_id)
                                    @can('sales.view')
                                        <a href="{{ route('sales.invoices.show', $entry->reference_id) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow text-xs ml-1">View</a>
                                    @endcan
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">
                                {{ bccomp((string) $entry->debit, '0', 2) > 0 ? number_format($entry->debit, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">
                                {{ bccomp((string) $entry->credit, '0', 2) > 0 ? number_format($entry->credit, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ number_format($entry->balance_after, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @include('sales.invoices._status', ['status' => $entry->status])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                No ledger entries found.
                                @if (bccomp($currentBalance, '0', 2) !== 0 && ! request()->hasAny(['from_date', 'to_date', 'entry_type']))
                                    <span class="block mt-1">Opening balance: {{ number_format($currentBalance, 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entries->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $entries->links() }}</div>
        @endif
    </div>
</x-app-layout>
