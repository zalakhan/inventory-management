<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">New Stock Receiving</h2>
    </x-slot>

    <div class="max-w-5xl">
        <form method="POST" action="{{ route('inventory.stock_receiving.store') }}" class="space-y-6">
            @csrf
            @include('inventory.stock_receiving._form', ['receiving' => null])
            <div class="flex gap-3">
                <x-primary-button>Save Draft</x-primary-button>
                <a href="{{ route('inventory.stock_receiving.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
