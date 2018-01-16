# laravel-base-repository
A configuration-based abstract repository with automatized processes that lighten and simplify your code.  
With this repository, easily create, update and destroy your entities with automatic related-files handling.  
This package provide the following features :
- Entity creation
- Entity edition
- Entity destruction
- Entity's files versionning on creation and edition
- Images resizing (sizes defined in `base-repository.php` config file) and optimizing

This package uses the following dependencies :
- Images optimization : https://github.com/spatie/laravel-image-optimizer
- Images manipulation : https://github.com/Intervention/image

## Installation
- Install the package with composer :
```bash
composer require okipa/laravel-base-repository
```

- Laravel 5.5+ uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.
If you don't use auto-discovery or if you use a Laravel 5.4- version, add the package service provider in the `register()` method from your `app/Providers/AppServiceProvider.php` :
```php
// laravel clever base repository
// https://github.com/Okipa/laravel-base-repository
$this->app->register(Okipa\LaravelBaseRepository\LaravelBaseRepositoryServiceProvider::class);
```

- Publish the package configuration
```
php artisan vendor:publish --tag=laravel-base-repository
```
