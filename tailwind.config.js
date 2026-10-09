import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    'deep-forest': '#022E24',
                    'moss-shadow': '#1F4D3A',
                    eucalyptus: '#4F8F75',
                    'sage-mist': '#9FC3B2',
                    'pale-lichen': '#E7F1EC',
                },
            },
        },
    },

    plugins: [forms],
};
