<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-brand-eucalyptus border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-moss-shadow focus:bg-brand-moss-shadow active:bg-brand-deep-forest focus:outline-none focus:ring-2 focus:ring-brand-sage-mist focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
