<?php

namespace Okipa\LaravelBootstrapTableList;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Okipa\LaravelCleverBaseRepository\LaravelCleverBaseRepository;

class LaravelCleverBaseRepositoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/repository.php' => config_path('repository.php'),
        ], 'LaravelCleverBaseRepository');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/repository.php', 'repository'
        );
        $this->app->singleton('Okipa\LaravelCleverBaseRepository', function(Application $app) {
            $laravelCleverBaseRepository = $app->make(LaravelCleverBaseRepository::class);

            return $laravelCleverBaseRepository;
        });
    }
}
