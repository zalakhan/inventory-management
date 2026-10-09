<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800">Reports</h2>
            <p class="mt-1 text-sm text-gray-500">Business reports and data exports.</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <a href="{{ route('reports.stock_summary') }}" class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm hover:border-brand-eucalyptus transition group">
            <h3 class="font-semibold text-brand-deep-forest group-hover:text-brand-eucalyptus">Stock Summary</h3>
            <p class="mt-2 text-sm text-gray-500">Current stock levels and value by product and warehouse.</p>
        </a>

        <a href="{{ route('reports.sales_register') }}" class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm hover:border-brand-eucalyptus transition group">
            <h3 class="font-semibold text-brand-deep-forest group-hover:text-brand-eucalyptus">Sales Register</h3>
            <p class="mt-2 text-sm text-gray-500">Posted sales invoices with totals and outstanding amounts.</p>
        </a>

        <a href="{{ route('reports.customer_outstanding') }}" class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm hover:border-brand-eucalyptus transition group">
            <h3 class="font-semibold text-brand-deep-forest group-hover:text-brand-eucalyptus">Customer Outstanding</h3>
            <p class="mt-2 text-sm text-gray-500">Customers with positive receivable balances.</p>
        </a>

        <a href="{{ route('reports.stock_movements') }}" class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm hover:border-brand-eucalyptus transition group">
            <h3 class="font-semibold text-brand-deep-forest group-hover:text-brand-eucalyptus">Stock Movement Report</h3>
            <p class="mt-2 text-sm text-gray-500">Posted stock IN/OUT movements with filters.</p>
        </a>

        <a href="{{ route('reports.payment_register') }}" class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm hover:border-brand-eucalyptus transition group">
            <h3 class="font-semibold text-brand-deep-forest group-hover:text-brand-eucalyptus">Payment Register</h3>
            <p class="mt-2 text-sm text-gray-500">Posted customer payments by date and method.</p>
        </a>
    </div>
</x-app-layout>
