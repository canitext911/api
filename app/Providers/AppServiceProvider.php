<?php

namespace App\Providers;

use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Http\ResponseFactory as LumenResponseFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return new LumenResponseFactory;
        });
    }
}
