<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-brand-deep-forest">Profile Settings</h2>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="p-6 bg-white shadow-sm rounded-2xl border border-brand-pale-lichen">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div id="password" class="p-6 bg-white shadow-sm rounded-2xl border border-brand-pale-lichen scroll-mt-24">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-6 bg-white shadow-sm rounded-2xl border border-brand-pale-lichen">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
