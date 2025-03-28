const defaultTheme = require("tailwindcss/defaultTheme");

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                secondary: "rgb(44, 169, 188)",
                "secondary-900": "rgb(0, 72, 81)",
                "secondary-800": "rgb(6, 96, 107)",
                "secondary-700": "rgb(0, 122, 137)",
                "secondary-600": "rgb(22, 150, 169)",
                "secondary-500": "rgb(44, 169, 188)",
                "secondary-400": "rgb(66, 184, 204)",
                "secondary-300": "rgb(89, 200, 217)",
                "secondary-200": "rgb(157, 219, 229)",
                "secondary-100": "rgb(182, 239, 246)",
            },
        },
    },

    plugins: [require("@tailwindcss/forms")],
};
