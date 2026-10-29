/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: ['class', '[data-theme="dark"]'],
    theme: {
        extend: {
            fontFamily: {
                serif: ['Newsreader', '"Source Serif 4"', 'Georgia', '"Times New Roman"', 'serif'],
                sans: ['Inter', 'system-ui', '-apple-system', '"Segoe UI"', 'Roboto', 'sans-serif'],
                mono: ['"JetBrains Mono"', 'ui-monospace', '"SF Mono"', 'Menlo', 'monospace'],
            },
            colors: {
                warm: {
                    bg: 'var(--bg)',
                    raised: 'var(--bg-raised)',
                    sunken: 'var(--bg-sunken)',
                    tint: 'var(--bg-tint)',
                    border: 'var(--border)',
                    'border-strong': 'var(--border-strong)',
                    text: 'var(--text)',
                    muted: 'var(--text-muted)',
                    faint: 'var(--text-faint)',
                },
                accent: {
                    DEFAULT: 'var(--accent)',
                    hover: 'var(--accent-hover)',
                    soft: 'var(--accent-soft)',
                    contrast: 'var(--accent-contrast)',
                },
                semantic: {
                    'success-text': 'var(--success-text)',
                    'success-bg': 'var(--success-bg)',
                    'warning-text': 'var(--warning-text)',
                    'warning-bg': 'var(--warning-bg)',
                    'error-text': 'var(--error-text)',
                    'error-bg': 'var(--error-bg)',
                    'info-text': 'var(--info-text)',
                    'info-bg': 'var(--info-bg)',
                    'purple-text': 'var(--purple-text)',
                    'purple-bg': 'var(--purple-bg)',
                }
            },
            boxShadow: {
                'elevation-1': 'var(--shadow-1)',
                'elevation-2': 'var(--shadow-2)',
                'elevation-3': 'var(--shadow-3)',
            },
            borderRadius: {
                sm: 'var(--radius-sm)',
                md: 'var(--radius-md)',
                lg: 'var(--radius-lg)',
                xl: 'var(--radius-xl)',
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
