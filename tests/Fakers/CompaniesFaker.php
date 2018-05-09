<?php

namespace Okipa\LaravelBaseRepository\Test\Fakers;

use Okipa\LaravelBaseRepository\Test\Models\Company;

trait CompaniesFaker
{
    public function createUniqueCompany()
    {
        return app(Company::class)->create($this->generateFakeCompanyData());
    }

    public function generateFakeCompanyData()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
