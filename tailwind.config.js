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
                inter: ['Inter', ...defaultTheme.fontFamily.sans],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        forms,
        function({ addUtilities }) {
            addUtilities({
                '.scrollbar-thin': {
                    'scrollbar-width': 'thin',
                    'scrollbar-color': '#4B5563 transparent',
                },
                '.scrollbar-thin::-webkit-scrollbar': {
                    width: '6px',
                },
                '.scrollbar-thin::-webkit-scrollbar-track': {
                    background: 'transparent',
                },
                '.scrollbar-thin::-webkit-scrollbar-thumb': {
                    'background-color': '#4B5563',
                    'border-radius': '3px',
                },
                '.scrollbar-thin::-webkit-scrollbar-thumb:hover': {
                    'background-color': '#6B7280',
                },
                '.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb': {
                    'background-color': '#4B5563',
                },
                '.scrollbar-track-transparent::-webkit-scrollbar-track': {
                    background: 'transparent',
                },
                '.line-clamp-1': {
                    overflow: 'hidden',
                    display: '-webkit-box',
                    '-webkit-box-orient': 'vertical',
                    '-webkit-line-clamp': '1',
                },
                '.line-clamp-2': {
                    overflow: 'hidden',
                    display: '-webkit-box',
                    '-webkit-box-orient': 'vertical',
                    '-webkit-line-clamp': '2',
                },
            })
        }
    ],
};
