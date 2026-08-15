import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                navy: {
                    DEFAULT: '#1A2B4C',
                    dark: '#0F1A30',
                },
                burgundy: {
                    DEFAULT: '#0C6C9C',
                    dark: '#0A5A83',
                    light: '#D9EEF6',
                },
                gold: {
                    DEFAULT: '#D4AF37',
                    light: '#E9CE7A',
                },
                // Brand blue ramp derived from the STTNI logo (#0C6C9C)
                blue: {
                    50: '#EFF8FB',
                    100: '#D9EEF6',
                    200: '#B6DFED',
                    300: '#85C8E0',
                    400: '#4EADCE',
                    500: '#1E90B8',
                    600: '#0C6C9C',
                    700: '#0A5A83',
                    800: '#0B4A6B',
                    900: '#0C3E5A',
                    950: '#08293E',
                },
                // Legacy indigo-* utilities are remapped to the same brand blue ramp
                // so the whole admin panel keeps its layout but wears the logo palette.
                indigo: {
                    50: '#EFF8FB',
                    100: '#D9EEF6',
                    200: '#B6DFED',
                    300: '#85C8E0',
                    400: '#4EADCE',
                    500: '#1E90B8',
                    600: '#0C6C9C',
                    700: '#0A5A83',
                    800: '#0B4A6B',
                    900: '#0C3E5A',
                    950: '#08293E',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Merriweather', 'Georgia', 'serif'],
            },
            boxShadow: {
                sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            },
        },
    },

    plugins: [forms],
};
