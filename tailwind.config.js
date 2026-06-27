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
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
            },
        },
    },

    plugins: [
        forms,
        function({ addUtilities }) {
            const newUtilities = {
                '.card-premium': {
                    backgroundColor: '#FDFBF7',
                    borderRadius: '0.75rem',
                    overflow: 'hidden',
                    transition: 'all 300ms cubic-bezier(0.4, 0, 0.2, 1)',
                },
                '.card-premium:hover': {
                    boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
                },
                '.btn-secondary': {
                    display: 'inline-block',
                    padding: '0.5rem 1rem',
                    fontSize: '0.875rem',
                    fontWeight: '500',
                    borderRadius: '9999px',
                    transition: 'all 300ms cubic-bezier(0.4, 0, 0.2, 1)',
                },
            };
            addUtilities(newUtilities, ['responsive']);
        }
    ],
};
