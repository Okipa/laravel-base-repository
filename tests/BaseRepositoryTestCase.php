<?php

namespace Okipa\LaravelBaseRepository\Test;

use Faker\Factory;
use Okipa\LaravelBaseRepository\Test\Repositories\UserRepository;
use Orchestra\Testbench\TestCase;

abstract class BaseRepositoryTestCase extends TestCase
{
    protected $faker;
    protected $repository;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path'     => realpath(__DIR__ . '/database/migrations'),
        ]);
        $this->faker = Factory::create();
        $this->repository = app(UserRepository::class);
    }
}
