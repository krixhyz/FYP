import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eef9f1',
                    100: '#d2eddc',
                    200: '#abdcbc',
                    300: '#83ca9b',
                    400: '#5ab979',
                    500: '#2fa756',
                    600: '#138a4d',
                    700: '#09864a',
                    800: '#006a38',
                    900: '#004a29',
                },
                accent: {
                    50: '#fafafa',
                    100: '#f3f3f3',
                    200: '#eeeeee',
                    300: '#e8e8e8',
                    400: '#e2e2e2',
                    500: '#dadada',
                    600: '#bdcabd',
                    700: '#a8b6a8',
                    800: '#7e8e7e',
                    900: '#596659',
                },
            },
            fontFamily: {
                sans: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                'soft': '0 32px 32px -28px rgba(26, 28, 28, 0.08)',
                'soft-lg': '0 32px 52px -30px rgba(26, 28, 28, 0.12)',
                'inner-soft': 'inset 0 0 0 1px rgba(189, 202, 189, 0.22)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-up': 'slideUp 0.5s ease-out',
                'slide-down': 'slideDown 0.5s ease-out',
                'scale-in': 'scaleIn 0.3s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(20px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-20px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                scaleIn: {
                    '0%': { transform: 'scale(0.95)', opacity: '0' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                },
            },
        },
    },

    plugins: [forms],
};
