<header class="bg-white border-b border-brand-pale-lichen sticky top-0 z-30">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 sm:px-6 lg:px-8 py-4">
        <div>
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
            @endphp
            <p class="text-sm text-gray-500">{{ $greeting }}</p>
            <h1 class="text-xl font-semibold text-brand-deep-forest">{{ Auth::user()->name }} 👋</h1>
        </div>

        <div class="flex items-center gap-3 sm:gap-4">
            <div class="hidden md:block relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" placeholder="Search..." disabled
                    class="pl-9 pr-4 py-2 w-56 rounded-lg border border-gray-200 bg-brand-pale-lichen/50 text-sm text-gray-500 cursor-not-allowed">
            </div>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 hover:bg-brand-pale-lichen transition">
                    <div class="h-8 w-8 rounded-full bg-brand-eucalyptus text-white flex items-center justify-center text-sm font-semibold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-medium text-brand-deep-forest">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ Auth::user()->roles->first()?->name ?? 'User' }}</div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false" x-cloak
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-brand-pale-lichen">Profile</a>
                    <a href="{{ route('profile.edit') }}#password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-brand-pale-lichen">Change Password</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
