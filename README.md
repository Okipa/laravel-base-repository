# laravel-base-repository
An abstract base repository with predefined common features.

[![Source Code](https://img.shields.io/badge/source-okipa/laravel--model--base--repository-blue.svg)](https://github.com/Okipa/laravel-base-repository)
[![Latest Version](https://img.shields.io/github/release/okipa/laravel-base-repository.svg?style=flat-square)](https://github.com/Okipa/laravel-base-repository/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/okipa/laravel-base-repository.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-base-repository)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://scrutinizer-ci.com/g/Okipa/laravel-base-repository/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-base-repository/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/Okipa/laravel-base-repository/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-base-repository/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Okipa/laravel-base-repository/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Okipa/laravel-base-repository/?branch=master)

------------------------------------------------------------------------------------------------------------------------

## Before starting
The repository pattern has several objectives :
- Encourage development good practices (separation of concerns, code reusability, ...)
- Improve code testability

Before using this package, you should be familiar with the repository pattern, and especially with its Laravel implementation.  
You can know more about it by reading the several articles you'll can find about this. Here is one among others : https://medium.com/@jsdecena/refactor-the-simple-tdd-in-laravel-a92dd48f2cdd

## Installation
- Install the package with composer :
```bash
composer require okipa/laravel-base-repository
```
- Create your projet `app/Providers/RepositoryServiceProvider.php` file. You can follow the example below :
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\User\UserRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // users
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // then, register all your other repositories here ...
    }
}
```

- Add your `RepositoryServiceProvider` to the providers declarations of the Laravel framework, in the `config/app.php` :
```php
// ...

'providers' => [
    // other provider declarations ...

    // custom providers
    App\Providers\RepositoryServiceProvider::class,
],

// ...
```

- Create an `app/Repositories` directory where you will store your different project repo.
- Create your project 'app/Repositories/BaseRepository.php` abstract class as following :
```php
namespace App\Repositories;

use Okipa\LaravelBaseRepository\BaseRepository;

abstract class BaseRepository extends BaseRepository
{
    // your base repository custom code ...
}
```
- Create an `app/Repositories/User/UserRepositoryInterface.php` interface :
```php
namespace App\Repositories\User;

use App\Repositories\UserRepositoryInterface;

interface UserRepositoryInterface
{

    /**
     * @param array $selected
     * @param array $data
     *
     * @return array
     */
    public function getListOfActiveEvents(array $selected, array $data): array;
}

```

------------------------------------------------------------------------------------------------------------------------

## Usage



## API

### Attributes

### Methods
