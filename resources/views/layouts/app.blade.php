<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Prime Distributions') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-brand-pale-lichen text-brand-deep-forest">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            @include('layouts.sidebar')

            <div class="flex-1 flex flex-col min-w-0 min-h-screen">
                {{-- Mobile top bar --}}
                <div class="lg:hidden bg-white border-b border-brand-pale-lichen px-4 py-3 flex items-center justify-between">
                    <button @click="sidebarOpen = true" class="text-brand-moss-shadow hover:text-brand-deep-forest">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="font-semibold text-brand-deep-forest text-sm">Prime Distributions</span>
                    <div class="w-6"></div>
                </div>

                @include('layouts.topbar')

                @isset($header)
                    <div class="px-4 sm:px-6 lg:px-8 pt-4">
                        {{ $header }}
                    </div>
                @endisset

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    <x-flash-messages />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
