let mix = require('laravel-mix');


mix.js('resources/js/app.js', 'dist/js')
    .postCss('resources/css/app.css', 'dist/css', [
        require('postcss-import'),
        require('tailwindcss')
    ]);

