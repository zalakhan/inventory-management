<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Category</h2>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('master.categories.update', $category) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('master.categories._form')
            <div class="flex gap-3">
                <x-primary-button>Update Category</x-primary-button>
                <a href="{{ route('master.categories.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
