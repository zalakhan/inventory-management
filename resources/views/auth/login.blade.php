<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Prime Distributions</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen bg-white">
    <div class="min-h-screen flex flex-col lg:flex-row">
        {{-- Hero panel --}}
        <div class="relative lg:w-1/2 bg-gradient-to-br from-brand-deep-forest via-brand-moss-shadow to-brand-eucalyptus text-white flex items-center justify-center px-8 py-12 lg:py-0">
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            <div class="relative max-w-md text-center lg:text-left">
                <x-brand-logo class="h-14 w-14 text-lg mx-auto lg:mx-0 mb-6" />
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight">Welcome to Prime Distributions</h1>
                <p class="mt-4 text-brand-sage-mist text-lg leading-relaxed">
                    Manage your inventory smarter, faster, and easier — all in one place.
                </p>
            </div>
        </div>

        {{-- Login form panel --}}
        <div class="flex-1 flex items-center justify-center px-6 py-10 lg:py-0 bg-brand-pale-lichen/30">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <x-brand-logo class="h-12 w-12 text-base mx-auto mb-4" />
                    <h2 class="text-2xl font-bold text-brand-deep-forest">Welcome Back</h2>
                    <p class="mt-1 text-sm text-gray-500">Sign in to your account to continue</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="bg-white rounded-2xl shadow-sm border border-brand-pale-lichen p-6 sm:p-8 space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="email" value="Email Address" class="text-brand-deep-forest" />
                        <x-text-input id="email" class="block mt-1.5 w-full rounded-lg border-gray-200 focus:border-brand-eucalyptus focus:ring-brand-eucalyptus" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password" class="text-brand-deep-forest" />
                        <x-text-input id="password" class="block mt-1.5 w-full rounded-lg border-gray-200 focus:border-brand-eucalyptus focus:ring-brand-eucalyptus" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-brand-eucalyptus focus:ring-brand-eucalyptus" name="remember">
                            <span class="ms-2 text-sm text-gray-600">Remember Me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-brand-eucalyptus hover:text-brand-moss-shadow font-medium" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-brand-eucalyptus text-white text-sm font-semibold rounded-lg hover:bg-brand-moss-shadow focus:outline-none focus:ring-2 focus:ring-brand-sage-mist focus:ring-offset-2 transition">
                        Log In
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-gray-400">&copy; {{ date('Y') }} Prime Distributions. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
