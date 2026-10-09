<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">New Customer Payment</h2>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('sales.payments.store') }}" class="space-y-6">
            @csrf
            @include('sales.payments._form', ['payment' => null])
            <div class="flex gap-3">
                <x-primary-button>Save Draft</x-primary-button>
                <a href="{{ route('sales.payments.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
