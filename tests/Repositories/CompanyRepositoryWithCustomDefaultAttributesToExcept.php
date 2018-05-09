<?php

namespace Okipa\LaravelBaseRepository\Test\Repositories;

use Okipa\LaravelBaseRepository\BaseRepository;
use Okipa\LaravelBaseRepository\Test\Models\Company;

class CompanyRepositoryWithCustomDefaultAttributesToExcept extends BaseRepository
{
    protected $model = Company::class;
    protected $defaultAttributesToExcept = ['name'];
}