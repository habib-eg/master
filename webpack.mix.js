const mix = require('laravel-mix');

mix
    .js('resources/js/app.js', 'assets/js')
    .sass('resources/sass/app.scss', 'assets/css')
;
