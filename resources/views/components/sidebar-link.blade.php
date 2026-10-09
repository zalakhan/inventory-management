@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center px-3 py-2.5 rounded-xl text-sm font-medium bg-brand-eucalyptus text-white shadow-sm'
    : 'flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-brand-sage-mist hover:bg-brand-moss-shadow hover:text-white transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
