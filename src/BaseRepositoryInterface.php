<?php

namespace Okipa\LaravelBaseRepository;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Set the repository model class to instantiate.
     *
     * @param string $modelClass
     *
     * @return $this
     */
    public function setModel(string $modelClass);

    /**
     * Set the repository request to use.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function setRequest(Request $request);

    /**
     * Create one or more model instances from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultipleFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = []);

    /**
     * Create one or more model instances from data array.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultipleFromArray(array $data);

    /**
     * Create or update a model instance from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function createOrUpdateFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = []);

    /**
     * Create or update a model instance from array data.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function createOrUpdateFromArray(array $data);

    /**
     * Update a model instance from its primary key
     *
     * @param int   $instancePrimary
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function updateByPrimary(int $instancePrimary, array $data);

    /**
     * Destroy a model instance from the request data.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return bool|null
     */
    public function deleteFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = []);

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteFromArray(array $data);

    /**
     * Delete a model instance from its primary key
     *
     * @param int $instancePrimary
     *
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteByPrimary(int $instancePrimary);

    /**
     * Delete multiple model instances from their primary keys.
     *
     * @param array $instancePrimaries
     *
     * @return int
     */
    public function deleteMultipleFromPrimaries(array $instancePrimaries);

    /**
     * Paginate array results.
     *
     * @param array $data
     * @param int   $perPage
     *
     * @return LengthAwarePaginator
     */
    public function paginateArrayResults(array $data, int $perPage = 50);

    /**
     * Find one model instance from its primary key value.
     *
     * @param int $instancePrimary
     *
     * @return mixed
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneByPrimary(int $instancePrimary);

    /**
     * Find one model instance from an associative array.
     *
     * @param array $data
     *
     * @return mixed
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneFromArray(array $data, $throwsExceptionIfNotFound = true);

    /**
     * Find multiple model instance from an associative array.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function findMultipleFromArray(array $data);

    /**
     * Get all model instances from database.
     *
     * @param array  $columns
     * @param string $orderBy
     * @param string $orderByDirection
     *
     * @return mixed
     */
    public function getAll($columns = ['*'], string $orderBy = 'default', string $orderByDirection = 'asc');
}
