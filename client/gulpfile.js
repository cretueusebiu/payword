var elixir = require('laravel-elixir');

elixir.config.assetsPath = '';

elixir(function(mix) {
    mix.browserify('app.js', 'js/dist.js');
});
