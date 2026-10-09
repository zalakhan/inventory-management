@php
    $classes = match ($status) {
        'draft' => 'bg-yellow-100 text-yellow-800',
        'posted' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp
<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ ucfirst($status) }}
</span>
