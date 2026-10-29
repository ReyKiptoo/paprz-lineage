/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Instrument Sans', 'system-ui', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
                serif: ['Newsreader', 'serif'],
            },
            colors: {
                paper: {
                    50: '#faf8f5',
                    100: '#f5f0ea',
                    200: '#e8ded2',
                    300: '#d6c5b0',
                    400: '#bfa78b',
                    500: '#ab8d6e',
                    600: '#947355',
                    700: '#795b43',
                    800: '#644b39',
                    900: '#533e31',
                    950: '#2d2019',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
