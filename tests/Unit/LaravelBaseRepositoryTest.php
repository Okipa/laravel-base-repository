<?php

namespace Okipa\LaravelBaseRepository\Test\Unit;

use Illuminate\Http\Request;
use Okipa\LaravelBaseRepository\Test\BaseRepositoryTestCase;
use Okipa\LaravelBaseRepository\Test\Fakers\CompaniesFaker;
use Okipa\LaravelBaseRepository\Test\Fakers\UsersFaker;
use Okipa\LaravelBaseRepository\Test\Models\Company;
use Okipa\LaravelBaseRepository\Test\Models\User;
use Okipa\LaravelBaseRepository\Test\Repositories\CompanyRepositoryWithCustomDefaultAttributesToExcept;
use Okipa\LaravelBaseRepository\Test\Repositories\CompanyRepositoryWithDisabledDefaultAttributesException;
use PDOException;

class TableListColumnTest extends BaseRepositoryTestCase
{
    use UsersFaker;
    use CompaniesFaker;

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

    public function testDeleteFromRequest()
    {
        $user = $this->createUniqueUser();
        $request = Request::create('test', 'GET', $user->toArray());
        $this->repository->setRequest($request);
        $user->remember_token = null;
        $this->assertEquals([$user->toArray()], app(User::class)->all()->toArray());
        $this->repository->deleteFromRequest();
        $this->assertEmpty(app(User::class)->all());
    }

    public function testUpdateFromPrimary()
    {
        $user = $this->createUniqueUser();
        $user->name = 'Jean';
        $user->remember_token = 'token';
        $updatedUser = $this->repository->updateFromPrimary($user->id, [
            'name'           => 'Jean',
            'remember_token' => 'token',
        ]);
        $this->assertEquals('Jean', $updatedUser->name);
        $this->assertEquals($user->email, $updatedUser->email);
        $this->assertEquals($user->password, $updatedUser->password);
        $this->assertEquals('token', $updatedUser->remember_token);
    }

    public function testDeleteFromPrimary()
    {
        $user = $this->createUniqueUser();
        $user->remember_token = null;
        $this->assertEquals([$user->toArray()], app(User::class)->all()->toArray());
        $this->repository->deleteFromPrimary($user->id);
        $this->assertEmpty(app(User::class)->all());
    }

    public function testDeleteAnotherModelFromPrimary()
    {
        $user = $this->createUniqueUser();
        $company = $this->createUniqueCompany();
        $user->remember_token = null;
        $company->_token = null;
        $company->_method = null;
        $this->assertEquals([$user->toArray()], app(User::class)->all()->toArray());
        $this->assertEquals([$company->toArray()], app(Company::class)->all()->toArray());
        $this->repository->setModel(Company::class);
        $this->repository->deleteFromPrimary($company->id);
        $this->assertEquals([$user->toArray()], app(User::class)->all()->toArray());
        $this->assertEmpty(app(Company::class)->all());
    }

    public function testDeleteMultipleFromPrimaries()
    {
        $users = $this->createMultipleUsers(5);
        $this->assertCount(5, $users);
        $ids = $users->pluck('id')->toArray();
        $this->repository->deleteMultipleFromPrimaries($ids);
        $this->assertEmpty(app(User::class)->all());
    }

    public function testDisableExceptDefaultAttributes()
    {
        $this->repository = app(CompanyRepositoryWithDisabledDefaultAttributesException::class);
        $data = array_merge([
            '_token'  => 'token',
            '_method' => 'update',
        ], $this->generateFakeCompanyData());
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $company = $this->repository->createOrUpdateFromRequest();
        $this->assertEquals($data['name'], $company->name);
        $this->assertEquals($data['_token'], $company->_token);
        $this->assertEquals($data['_method'], $company->_method);
    }

    /**
     * @expectedException PDOException
     * @expectedExceptionMessage Integrity constraint violation: 19 NOT NULL constraint failed: companies.name
     */
    public function testCustomizeDefaultAttributesToExcept()
    {
        $this->repository = app(CompanyRepositoryWithCustomDefaultAttributesToExcept::class);
        $data = array_merge([
            '_token'  => 'token',
            '_method' => 'update',
        ], $this->generateFakeCompanyData());
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $this->repository->createOrUpdateFromRequest();
    }
}
