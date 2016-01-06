process.env.DISABLE_NOTIFIER = true;

var elixir = require('laravel-elixir');

elixir.config.assetsPath = '';
elixir.config.sourcemaps = true;

elixir(function(mix) {
    mix.browserify('app.js', 'js/dist.js', null, {debug: true});
});
