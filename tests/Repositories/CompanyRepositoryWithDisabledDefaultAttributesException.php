<?php

namespace Okipa\LaravelBaseRepository\Test\Repositories;

use Okipa\LaravelBaseRepository\BaseRepository;
use Okipa\LaravelBaseRepository\Test\Models\Company;

class CompanyRepositoryWithDisabledDefaultAttributesException extends BaseRepository
{
    protected $model = Company::class;
    protected $exceptDefaultAttributes = false;
}