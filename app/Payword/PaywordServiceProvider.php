<?php

namespace App\Payword;

use Illuminate\Support\ServiceProvider;

class PaywordServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('broker', function ($app) {
            return new Broker;
        });

        $this->app->alias('broker', Broker::class);
    }
}
