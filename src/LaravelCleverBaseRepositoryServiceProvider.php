<?php

namespace Okipa\LaravelBootstrapTableList;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageServiceProvider;
use Okipa\LaravelCleverBaseRepository\LaravelCleverBaseRepository;

class LaravelCleverBaseRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/repository.php' => config_path('repository.php'),
        ], 'LaravelCleverBaseRepository');
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/repository.php', 'repository');
        $this->app->singleton('Okipa\LaravelCleverBaseRepository', function(Application $app) {
            return $app->make(LaravelCleverBaseRepository::class);
        });
        // we load the intervention image package
        // https://github.com/Intervention/image
        $this->app->register(ImageServiceProvider::class);
        $this->app->alias('Image', Image::class);
    }
}
