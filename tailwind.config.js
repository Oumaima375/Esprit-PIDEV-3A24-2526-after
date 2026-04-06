/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx,ts,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: '#005689',
          dark: '#003b5f',
          light: '#0a79b8',
          mist: '#e8f1f6',
        },
        success: '#28A745',
        warning: '#D35400',
        sand: '#A68F73',
        surface: '#F8F9FA',
      },
      boxShadow: {
        soft: '0 20px 45px -24px rgba(0, 86, 137, 0.35)',
      },
      fontFamily: {
        sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      backgroundImage: {
        hero: 'radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 35%), linear-gradient(135deg, #005689 0%, #003b5f 100%)',
      },
    },
  },
  plugins: [],
};
