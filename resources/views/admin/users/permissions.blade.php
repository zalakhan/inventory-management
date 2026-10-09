<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Manage Permissions</h2>
    </x-slot>

    <div class="max-w-6xl space-y-6">
        {{-- User summary card --}}
        <div class="bg-white shadow-sm rounded-lg p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 font-medium">Name</dt>
                    <dd class="mt-1 text-gray-900 font-semibold">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium">Role</dt>
                    <dd class="mt-1">
                        @if ($user->roles->isNotEmpty())
                            @foreach ($user->roles as $role)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-800 text-white">{{ $role->name }}</span>
                            @endforeach
                        @else
                            <span class="text-gray-400">No role</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium">Email</dt>
                    <dd class="mt-1 text-gray-900">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium">Default Warehouse</dt>
                    <dd class="mt-1 text-gray-900">{{ $user->defaultWarehouse?->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <p class="text-sm text-gray-600">
                Check the actions this user is allowed to perform. Permissions are saved directly on the user account.
                @if ($user->roles->isNotEmpty())
                    The user also inherits permissions from their assigned role(s).
                @endif
            </p>

            <x-permission-matrix
                :modules="$modules"
                :action-columns="$actionColumns"
                :selected-permissions="old('permissions', $selectedPermissions)"
            />

            <x-input-error :messages="$errors->get('permissions')" class="mt-1" />

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <x-primary-button>Save Permissions</x-primary-button>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                    Back to Users
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
