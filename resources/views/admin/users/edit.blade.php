<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit User: {{ $user->name }}</h2>
    </x-slot>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.users._form')
            <div class="flex flex-wrap items-center gap-3">
                <x-primary-button>Update User</x-primary-button>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                @if ($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="ml-auto" onsubmit="return confirm('Delete this user?')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Delete User</x-danger-button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</x-app-layout>
