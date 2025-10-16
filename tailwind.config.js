/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
    "./build/**/*.{js,jsx,ts,tsx}",
    "./*.php",
    "./includes/**/*.php",
    "./admin/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        "calendar-blue": "#3b82f6",
        "calendar-green": "#10b981",
        "calendar-yellow": "#f59e0b",
        "calendar-red": "#ef4444",
        "calendar-purple": "#8b5cf6",
        "calendar-pink": "#ec4899",
      },
      fontFamily: {
        inter: ["Inter", "sans-serif"],
      },
      boxShadow: {
        calendar: "3px 3px 0px 0px rgba(0,0,0,1)",
      },
    },
  },
  plugins: [],
};
