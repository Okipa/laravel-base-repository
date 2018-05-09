<?php

namespace Okipa\LaravelBaseRepository\Test\Unit;

use Illuminate\Http\Request;
use Okipa\LaravelBaseRepository\Test\BaseRepositoryTestCase;
use Okipa\LaravelBaseRepository\Test\Fakers\UsersFaker;

class TableListColumnTest extends BaseRepositoryTestCase
{
    use UsersFaker;

    public function testCreateMultipleFromArray()
    {
        $data = [
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
        ];
        $users = $this->repository->createMultipleFromArray($data);
        $this->assertCount(count($data), $users);
        foreach ($data as $key => $user) {
            $this->assertEquals($data[$key]['name'], $users->get($key)->name);
            $this->assertEquals($data[$key]['email'], $users->get($key)->email);
            $this->assertEquals($data[$key]['password'], $users->get($key)->password);
        }
    }

    public function testCreateMultipleFromRequest()
    {
        $data = [
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
        ];
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $users = $this->repository->createMultipleFromRequest();
        $this->assertCount(count($data), $users);
        foreach ($data as $key => $user) {
            $this->assertEquals($data[$key]['name'], $users->get($key)->name);
            $this->assertEquals($data[$key]['email'], $users->get($key)->email);
            $this->assertEquals($data[$key]['password'], $users->get($key)->password);
        }
    }

    public function testCreateMultipleFromRequestWithAttributesExceptionAndAddition()
    {
        $data = [
            '_token'  => 'token',
            '_method' => 'update',
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
        ];
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $users = $this->repository->createMultipleFromRequest([
            '1',
            '2',
        ], [
            '0.name'           => 'Michel',
            '0.remember_token' => 'token',
        ]);
        $this->assertCount(1, $users);
        $this->assertEquals('Michel', $users->get(0)->name);
        $this->assertEquals($data[0]['email'], $users->get(0)->email);
        $this->assertEquals($data[0]['password'], $users->get(0)->password);
        $this->assertEquals('token', $users->get(0)->remember_token);
    }

    public function testCreateSingleFromRequest()
    {
        $data = $this->generateFakeUserData();
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $user = $this->repository->createOrUpdateFromRequest();
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['password'], $user->password);
    }

    public function testUpdateSingleFromRequest()
    {
        $user = $this->createUniqueUser();
        $user->name = 'Jean';
        $user->remember_token = 'token';
        $request = Request::create('test', 'GET', $user->toArray());
        $this->repository->setRequest($request);
        $updatedUser = $this->repository->createOrUpdateFromRequest();
        $this->assertEquals('Jean', $updatedUser->name);
        $this->assertEquals($user->email, $updatedUser->email);
        $this->assertEquals($user->password, $updatedUser->password);
        $this->assertEquals('token', $updatedUser->remember_token);
    }
    
    //    public function testSetModel()
    //    {
    //        $this->repository->setModel(Company::class);
    //        dd($this->repository);
    //    }
}
