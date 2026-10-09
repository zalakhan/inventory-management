<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Payment Register</h2>
                <p class="mt-1 text-sm text-gray-500">Posted customer payments.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">All Reports</a>
                @include('reports._export-button')
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <select name="customer_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->code }} — {{ $customer->name }}</option>
                @endforeach
            </select>
            <select name="payment_method" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All methods</option>
                @foreach (['cash', 'bank_transfer', 'cheque', 'other'] as $method)
                    <option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ str_replace('_', ' ', ucfirst($method)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $payment->receipt_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->payment_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->reference_no ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No posted payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $payments->links() }}</div>
        @endif
    </div>
</x-app-layout>
