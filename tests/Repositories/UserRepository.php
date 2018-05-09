<?php

namespace Okipa\LaravelBaseRepository\Test\Repositories;

use Okipa\LaravelBaseRepository\BaseRepository;
use Okipa\LaravelBaseRepository\Test\Models\User;

class UserRepository extends BaseRepository
{
    protected $model = User::class;
}