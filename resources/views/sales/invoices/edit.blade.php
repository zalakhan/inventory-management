<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit {{ $sale->invoice_number }}</h2>
    </x-slot>

    <div class="max-w-5xl">
        <form method="POST" action="{{ route('sales.invoices.update', $sale) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('sales.invoices._form', ['sale' => $sale])
            <div class="flex gap-3">
                <x-primary-button>Update Draft</x-primary-button>
                <a href="{{ route('sales.invoices.show', $sale) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
