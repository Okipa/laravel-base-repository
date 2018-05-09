<?php

namespace Okipa\LaravelBaseRepository\Test\Fakers;

use Hash;
use Illuminate\Support\Collection;
use Okipa\LaravelBaseRepository\Test\Models\User;

trait UsersFaker
{
    public function createMultipleUsers(int $count)
    {
        $users = new Collection();
        for ($ii = 0; $ii < $count; $ii++) {
            $users->push($this->createUniqueUser());
        }

        return $users;
    }

    public function createUniqueUser()
    {
        return  app(User::class)->create($this->generateFakeUserData());
    }

    public function generateFakeUserData()
    {
        return [
            'name'     => $this->faker->name,
            'email'    => $this->faker->email,
            'password' => Hash::make($this->faker->password),
        ];
    }
}
