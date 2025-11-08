/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      maxWidth: {
        '3/4': '75%',
      },
      zIndex: {
        '9999': '9999',
      },
      fontFamily: {
        'sans': ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
      }
    },
  },
  plugins: [
    function ({ addVariant }) {
      addVariant('not-empty', '&:not(:empty)'); // Custom variant for not(:empty)
    },
  ],
}