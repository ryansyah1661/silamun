/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        // Warna kustom dari design system Gambar 2
        'marine-primary': '#0D9488',
        'marine-primary-hover': '#0B7A70',
        'marine-secondary': '#22C55E',
        'marine-tertiary': '#F97316',
        'marine-neutral': '#64748B',
        'marine-bg': '#F4F7FC',
        'marine-dark': '#0F172A',
      },
      fontFamily: {
        // Font kustom dari design system Gambar 2
        sans: ['var(--font-inter)', 'sans-serif'],
        headline: ['var(--font-plus-jakarta-sans)', 'sans-serif'],
      },
    },
  },
  plugins: [],
};