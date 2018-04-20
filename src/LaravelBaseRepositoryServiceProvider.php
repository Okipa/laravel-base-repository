<?php

namespace Okipa\LaravelBaseRepository;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LaravelBaseRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // we publish the config on demand
        $this->publishes([
            __DIR__ . '/../config/base-repository.php' => config_path('base-repository.php'),
        ], 'laravel-base-repository');
    }

    /**
     * Register any application services.
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        // we merge the custom configurations to the default ones
        $this->mergeConfigFrom(__DIR__ . '/../config/base-repository.php', 'base-repository');
        // we instantiate the package
        $this->app->singleton('Okipa\LaravelBaseRepository', function(Application $app) {
            return $app->make(LaravelBaseRepository::class);
        });
    }
}
