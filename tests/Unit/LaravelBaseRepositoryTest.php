<?php

namespace Okipa\LaravelBaseRepository\Test\Unit;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Okipa\LaravelBaseRepository\Test\BaseRepositoryTestCase;
use Okipa\LaravelBaseRepository\Test\Fakers\CompaniesFaker;
use Okipa\LaravelBaseRepository\Test\Fakers\UsersFaker;
use Okipa\LaravelBaseRepository\Test\Models\Company;
use Okipa\LaravelBaseRepository\Test\Models\User;
use Okipa\LaravelBaseRepository\Test\Repositories\CompanyRepositoryWithCustomDefaultAttributesToExcept;
use Okipa\LaravelBaseRepository\Test\Repositories\CompanyRepositoryWithDisabledDefaultAttributesException;

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
     * @expectedException \PDOException
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

    public function testPaginateArrayResults()
    {
        $users = $this->createMultipleUsers(35);
        $paginatedUsersPageOne = $this->repository->paginateArrayResults($users->toArray(), 20);
        $this->assertCount(20, $paginatedUsersPageOne);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginatedUsersPageOne);

        $request = Request::create('test', 'GET', [
            'page' => 2,
        ]);
        $this->repository->setRequest($request);
        $paginatedUsersPageTwo = $this->repository->paginateArrayResults($users->toArray(), 20);
        $this->assertCount(15, $paginatedUsersPageTwo);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginatedUsersPageTwo);
        $this->assertCount(
            20,
            array_diff($paginatedUsersPageOne->pluck('id')->toArray(), $paginatedUsersPageTwo->pluck('id')->toArray())
        );
    }

    public function testFindOneFromPrimary()
    {
        $this->createMultipleUsers(5);
        $user = app(User::class)->find(rand(1, 5));
        $foundUser = $this->repository->findOneFromPrimary($user->id);
        $this->assertEquals($user, $foundUser);
    }

    public function testFindOneFromArray()
    {
        $this->createMultipleUsers(5);
        $user = app(User::class)->find(rand(1, 5));
        $foundUser = $this->repository->findOneFromArray(['id' => $user->id]);
        $this->assertEquals($user, $foundUser);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage No query results for model [Okipa\LaravelBaseRepository\Test\Models\User].
     */
    public function testFindOneFromArrayFail()
    {
        $this->repository->findOneFromArray(['id' => 1]);
    }

    public function testFindMultipleFromArray()
    {
        $data = $this->generateFakeUserData();
        app(User::class)->create($data);
        $data['email'] = $this->faker->email;
        app(User::class)->create($data);
        $users = $this->repository->findMultipleFromArray([
            'name'           => $data['name'],
            'remember_token' => null,
        ]);
        $this->assertCount(2, $users);
    }

    public function testGetAll()
    {
        $users = $this->createMultipleUsers(15);
        $users = $users->sortByDesc('name')->pluck('name');
        $foundUsers = $this->repository->getAll(['name'], 'name', 'desc')->pluck('name');
        $this->assertEquals($users, $foundUsers);
    }
}
