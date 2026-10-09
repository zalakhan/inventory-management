<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Adjustment — {{ $adjustment->adjustment_number }}</h2>
    </x-slot>

    <div class="max-w-5xl">
        <form method="POST" action="{{ route('inventory.stock_adjustments.update', $adjustment) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('inventory.stock_adjustments._form')
            <div class="flex gap-3">
                <x-primary-button>Update Draft</x-primary-button>
                <a href="{{ route('inventory.stock_adjustments.show', $adjustment) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
