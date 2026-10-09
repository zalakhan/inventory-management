<x-app-layout>
    {{-- KPI stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl border border-brand-pale-lichen p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Stock Value</p>
                    <p class="mt-2 text-3xl font-bold text-brand-deep-forest">{{ number_format((float) $totalStockValue, 2) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-brand-pale-lichen flex items-center justify-center text-brand-eucalyptus">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            @can('inventory.view')
                <a href="{{ route('inventory.stock.index') }}" class="mt-3 inline-block text-xs text-brand-eucalyptus font-medium hover:underline">View current stock</a>
            @endcan
        </div>

        <div class="bg-white rounded-2xl border border-brand-pale-lichen p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Today's Sales</p>
                    <p class="mt-2 text-3xl font-bold text-brand-deep-forest">{{ number_format((float) $todaysSales, 2) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-brand-pale-lichen flex items-center justify-center text-brand-eucalyptus">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            @can('sales.view')
                <a href="{{ route('sales.invoices.index') }}" class="mt-3 inline-block text-xs text-brand-eucalyptus font-medium hover:underline">View sales invoices</a>
            @endcan
        </div>

        <div class="bg-white rounded-2xl border border-brand-pale-lichen p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Low Stock Items</p>
                    <p class="mt-2 text-3xl font-bold @if($lowStockCount > 0) text-red-600 @else text-brand-deep-forest @endif">{{ $lowStockCount }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-brand-pale-lichen flex items-center justify-center text-brand-eucalyptus">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            @can('inventory.view')
                <a href="{{ route('inventory.stock.index', ['low_stock' => 1]) }}" class="mt-3 inline-block text-xs text-brand-eucalyptus font-medium hover:underline">View low stock</a>
            @endcan
        </div>

        <div class="bg-white rounded-2xl border border-brand-pale-lichen p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Outstanding Receivables</p>
                    <p class="mt-2 text-3xl font-bold text-brand-deep-forest">{{ number_format((float) $outstandingReceivables, 2) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-brand-pale-lichen flex items-center justify-center text-brand-eucalyptus">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            @can('reports.view')
                <a href="{{ route('reports.customer_outstanding') }}" class="mt-3 inline-block text-xs text-brand-eucalyptus font-medium hover:underline">View outstanding report</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Overview & activity --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-brand-deep-forest">Overview</h2>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                    Welcome to <strong>{{ \App\Models\Setting::getValue('company_name', 'Prime Distributions') }}</strong> inventory management.
                    Use the sidebar to manage stock, sales, payments, and reports.
                </p>
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-brand-pale-lichen p-4">
                        <p class="text-xs font-medium text-brand-moss-shadow uppercase tracking-wide">Your Role</p>
                        <p class="mt-1 text-sm font-semibold text-brand-deep-forest">{{ Auth::user()->roles->pluck('name')->join(', ') ?: 'No role assigned' }}</p>
                    </div>
                    <div class="rounded-xl bg-brand-pale-lichen p-4">
                        <p class="text-xs font-medium text-brand-moss-shadow uppercase tracking-wide">Default Warehouse</p>
                        <p class="mt-1 text-sm font-semibold text-brand-deep-forest">{{ Auth::user()->defaultWarehouse?->name ?? 'Not set' }}</p>
                    </div>
                </div>
            </div>

            @can('sales.view')
                <div class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-brand-deep-forest">Recent Sales</h2>
                        <a href="{{ route('sales.invoices.index') }}" class="text-sm text-brand-eucalyptus hover:underline">View all</a>
                    </div>
                    @if ($recentSales->isEmpty())
                        <p class="text-sm text-gray-500">No posted sales yet.</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($recentSales as $sale)
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</p>
                                        <p class="text-xs text-gray-500">{{ $sale->customer?->name }} · {{ $sale->sale_date?->format('M d, Y') }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-brand-deep-forest">{{ number_format($sale->total_amount, 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endcan
        </div>

        {{-- Quick actions & alerts --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-brand-pale-lichen p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-brand-deep-forest mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    @can('sales.create')
                        <a href="{{ route('sales.invoices.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-brand-pale-lichen transition group">
                            <div class="h-9 w-9 rounded-lg bg-brand-eucalyptus/10 text-brand-eucalyptus flex items-center justify-center group-hover:bg-brand-eucalyptus group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <span class="text-sm font-medium text-brand-deep-forest">New Sales Invoice</span>
                        </a>
                    @endcan
                    @can('stock_receiving.create')
                        <a href="{{ route('inventory.stock_receiving.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-brand-pale-lichen transition group">
                            <div class="h-9 w-9 rounded-lg bg-brand-eucalyptus/10 text-brand-eucalyptus flex items-center justify-center group-hover:bg-brand-eucalyptus group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                            <span class="text-sm font-medium text-brand-deep-forest">Receive Stock</span>
                        </a>
                    @endcan
                    @can('reports.view')
                        <a href="{{ route('reports.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-brand-pale-lichen transition group">
                            <div class="h-9 w-9 rounded-lg bg-brand-eucalyptus/10 text-brand-eucalyptus flex items-center justify-center group-hover:bg-brand-eucalyptus group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-brand-deep-forest">View Reports</span>
                        </a>
                    @endcan
                    @can('settings.manage')
                        <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-brand-pale-lichen transition group">
                            <div class="h-9 w-9 rounded-lg bg-brand-eucalyptus/10 text-brand-eucalyptus flex items-center justify-center group-hover:bg-brand-eucalyptus group-hover:text-white transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-brand-deep-forest">System Settings</span>
                        </a>
                    @endcan
                </div>
            </div>

            @if ($lowStockItems->isNotEmpty())
                <div class="bg-white rounded-2xl border border-red-200 p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-red-700 mb-4">Low Stock Alerts</h2>
                    <div class="space-y-3">
                        @foreach ($lowStockItems as $item)
                            <div class="text-sm">
                                <p class="font-medium text-gray-900">{{ $item->product?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->warehouse?->name }} · Qty: {{ number_format($item->quantity, 2) }} / Min: {{ number_format($item->product?->min_stock_level ?? 0, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
