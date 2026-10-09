{{-- Sidebar overlay for mobile --}}
<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
    <div @click="sidebarOpen = false" class="fixed inset-0 bg-brand-deep-forest/60"></div>
</div>

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-brand-deep-forest text-white transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:flex lg:flex-col shrink-0"
>
    <div class="flex items-center gap-3 h-16 px-4 border-b border-brand-moss-shadow">
        <x-brand-logo class="h-9 w-9 text-sm" />
        <div class="min-w-0">
            <a href="{{ route('dashboard') }}" class="block font-semibold text-sm text-white truncate">Prime Distributions</a>
            <span class="block text-xs text-brand-sage-mist truncate">Inventory Panel</span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-brand-sage-mist hover:text-white">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        @can('dashboard.view')
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-sidebar-link>
        @endcan

        @if(auth()->user()->can('products.view') || auth()->user()->can('categories.view') || auth()->user()->can('customers.view') || auth()->user()->can('suppliers.view') || auth()->user()->can('warehouses.view') || auth()->user()->can('areas.view') || auth()->user()->can('salesmen.view'))
            <p class="px-3 pt-5 pb-2 text-xs font-semibold uppercase tracking-wider text-brand-sage-mist/70">Master Data</p>

            @can('categories.view')
                <x-sidebar-link :href="route('master.categories.index')" :active="request()->routeIs('master.categories.*')">
                    Categories
                </x-sidebar-link>
            @endcan

            @can('products.view')
                <x-sidebar-link :href="route('master.products.index')" :active="request()->routeIs('master.products.*')">
                    Products
                </x-sidebar-link>
            @endcan

            @can('customers.view')
                <x-sidebar-link :href="route('master.customers.index')" :active="request()->routeIs('master.customers.*')">
                    Customers
                </x-sidebar-link>
            @endcan

            @can('suppliers.view')
                <x-sidebar-link :href="route('master.suppliers.index')" :active="request()->routeIs('master.suppliers.*')">
                    Suppliers
                </x-sidebar-link>
            @endcan

            @can('warehouses.view')
                <x-sidebar-link :href="route('master.warehouses.index')" :active="request()->routeIs('master.warehouses.*')">
                    Warehouses
                </x-sidebar-link>
            @endcan

            @can('areas.view')
                <x-sidebar-link :href="route('master.areas.index')" :active="request()->routeIs('master.areas.*')">
                    Areas
                </x-sidebar-link>
            @endcan

            @can('salesmen.view')
                <x-sidebar-link :href="route('master.salesmen.index')" :active="request()->routeIs('master.salesmen.*')">
                    Salesmen
                </x-sidebar-link>
            @endcan
        @endif

        @if(auth()->user()->can('stock_receiving.view') || auth()->user()->can('inventory.view') || auth()->user()->can('stock_adjustments.view'))
            <p class="px-3 pt-5 pb-2 text-xs font-semibold uppercase tracking-wider text-brand-sage-mist/70">Inventory</p>

            @can('stock_receiving.view')
                <x-sidebar-link :href="route('inventory.stock_receiving.index')" :active="request()->routeIs('inventory.stock_receiving.*')">
                    Stock Receiving
                </x-sidebar-link>
            @endcan

            @can('stock_adjustments.view')
                <x-sidebar-link :href="route('inventory.stock_adjustments.index')" :active="request()->routeIs('inventory.stock_adjustments.*')">
                    Stock Adjustments
                </x-sidebar-link>
            @endcan

            @can('inventory.view')
                <x-sidebar-link :href="route('inventory.stock.index')" :active="request()->routeIs('inventory.stock.*')">
                    Current Stock
                </x-sidebar-link>
                <x-sidebar-link :href="route('inventory.movements.index')" :active="request()->routeIs('inventory.movements.*')">
                    Stock Ledger
                </x-sidebar-link>
            @endcan
        @endif

        @if(auth()->user()->can('sales.view') || auth()->user()->can('customers.view') || auth()->user()->can('payments.view') || auth()->user()->can('sale_returns.view'))
            <p class="px-3 pt-5 pb-2 text-xs font-semibold uppercase tracking-wider text-brand-sage-mist/70">Sales</p>

            @can('sales.view')
                <x-sidebar-link :href="route('sales.invoices.index')" :active="request()->routeIs('sales.invoices.*')">
                    Sales Invoices
                </x-sidebar-link>
            @endcan

            @can('payments.view')
                <x-sidebar-link :href="route('sales.payments.index')" :active="request()->routeIs('sales.payments.*')">
                    Customer Payments
                </x-sidebar-link>
            @endcan

            @can('sale_returns.view')
                <x-sidebar-link :href="route('sales.returns.index')" :active="request()->routeIs('sales.returns.*')">
                    Sale Returns
                </x-sidebar-link>
            @endcan

            @can('customers.view')
                <x-sidebar-link :href="route('sales.customer_ledger.index')" :active="request()->routeIs('sales.customer_ledger.*')">
                    Customer Ledger
                </x-sidebar-link>
            @endcan
        @endif

        @can('reports.view')
            <p class="px-3 pt-5 pb-2 text-xs font-semibold uppercase tracking-wider text-brand-sage-mist/70">Reports</p>
            <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                Reports
            </x-sidebar-link>
        @endcan

        @if(auth()->user()->can('users.view') || auth()->user()->can('roles.manage') || auth()->user()->can('permissions.manage') || auth()->user()->can('settings.manage'))
            <p class="px-3 pt-5 pb-2 text-xs font-semibold uppercase tracking-wider text-brand-sage-mist/70">Administration</p>

            @can('settings.manage')
                <x-sidebar-link :href="route('admin.settings.edit')" :active="request()->routeIs('admin.settings.*')">
                    System Settings
                </x-sidebar-link>
            @endcan

            @can('users.view')
                <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    Users
                </x-sidebar-link>
            @endcan

            @can('roles.manage')
                <x-sidebar-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">
                    Roles
                </x-sidebar-link>
            @endcan

            @can('permissions.manage')
                <x-sidebar-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.*')">
                    Permissions
                </x-sidebar-link>
            @endcan
        @endif
    </nav>

    <div class="border-t border-brand-moss-shadow p-4 bg-brand-moss-shadow/30">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-brand-eucalyptus flex items-center justify-center text-sm font-semibold">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-brand-sage-mist truncate">{{ Auth::user()->roles->first()?->name ?? 'User' }}</div>
            </div>
        </div>
        <div class="mt-3 flex flex-col gap-1 text-sm">
            <a href="{{ route('profile.edit') }}" class="text-brand-sage-mist hover:text-white transition">Profile</a>
            <a href="{{ route('profile.edit') }}#password" class="text-brand-sage-mist hover:text-white transition">Change Password</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-brand-sage-mist hover:text-white transition">Log Out</button>
            </form>
        </div>
    </div>
</aside>
