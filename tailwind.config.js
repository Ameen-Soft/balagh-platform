import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Cairo', 'Tajawal', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                yemen: {
                    red: {
                        DEFAULT: '#CE1126',
                        50: '#fef2f2',
                        100: '#fee2e2',
                        200: '#fecaca',
                        500: '#ef4444',
                        600: '#dc2626',
                        700: '#b91c1c',
                        800: '#991b1b',
                        900: '#7f1d1d',
                    },
                    black: {
                        DEFAULT: '#000000',
                        light: '#18181b',
                        800: '#18181b',
                        900: '#09090b',
                        950: '#050507',
                    },
                    gold: {
                        DEFAULT: '#D97706',
                        50: '#fffbeb',
                        500: '#f59e0b',
                        600: '#d97706',
                    },
                    emerald: {
                        DEFAULT: '#059669',
                        50: '#ecfdf5',
                        500: '#10b981',
                        600: '#059669',
                    },
                },
            },
        },
    },

    plugins: [forms, typography],
};
