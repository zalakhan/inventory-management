<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Supplier</h2>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('master.suppliers.update', $supplier) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('master.suppliers._form')
            <div class="flex gap-3">
                <x-primary-button>Update Supplier</x-primary-button>
                <a href="{{ route('master.suppliers.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
