# laravel-base-repository
A configuration-based abstract repository with automated processes that lighten and simplify your code.  
With this repository, easily handle your entities.  
This package provide methods for the following features :
- Entity create
- Entity update
- Entity destroy
- Choice between json or database storage
- Automated entity's files renaming and storage
- Automated entity's images renaming, optimizing and storage

This package uses the following dependencies :
- Images optimization : https://github.com/spatie/laravel-image-optimizer
- Images manipulation : https://github.com/Intervention/image

------------------------------------------------------------------------------------------------------------------------

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
```php
php artisan vendor:publish --tag=laravel-base-repository
```

- Extends your own BaseRepository with the `Okipa\LaravelBaseRepository\LaravelBaseRepository` package file to empower your repositories.
```php
namespace App\Repositories;

use Okipa\LaravelBaseRepository\LaravelBaseRepository;

class BaseRepository extends LaravelBaseRepository
{
    // your base repository code ...
}
```

- Define the `$configKey` and the `$model` variables in your repository as shown in the example bellow.
```php
namespace App\Repositories\Users;

class UserRepository extends UnicornRepository implements UserRepositoryInterface
{

    protected $configKey = 'users';
    protected $model = User::class;
    
    // your user repository code ...
}
```
- Set the repository configuration in the `config/base-repository.php` file. Check and personalize the `users` example to create your other repositories configurations.

------------------------------------------------------------------------------------------------------------------------

## Usage

## API

### Eloquent overlay

### Json storage

### Images management

### Files management
