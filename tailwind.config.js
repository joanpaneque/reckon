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
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'dark-primary': '#0F0F0F',
                'dark-secondary': '#1A1A1A',
                'dark-border': '#222222',
                'dark-input-border': '#333333',
                'text-primary': '#FFFFFF',
                'text-secondary': '#B3B3B3',
                'text-tertiary': '#777777',
                'accent': '#00FFB3',
                'accent-hover': '#00E6A5',
                'accent-active': '#00CC94',
                'disabled-bg': '#333333',
                'disabled-text': '#777777',
            },
            borderRadius: {
                'modern': '12px',
            },
            fontSize: {
                'h1': '40px',
                'h2': '28px',
                'h3': '20px',
            },
            letterSpacing: {
                'wide-modern': '0.02em',
            },
            lineHeight: {
                'modern': '1.6',
            },
            transitionDuration: {
                'modern': '200ms',
            },
            boxShadow: {
                'modern': '0 2px 6px rgba(0,0,0,0.3)',
                'modern-hover': '0 4px 10px rgba(0,255,179,0.2)',
                'modern-focus': '0 0 6px rgba(0,255,179,0.25)',
            },
        },
    },

    plugins: [forms],
};
