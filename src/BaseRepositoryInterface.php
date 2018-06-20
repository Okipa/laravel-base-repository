<?php

namespace Okipa\LaravelBaseRepository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Set the repository model class to instantiate.
     *
     * @param string $modelClass
     *
     * @return \Okipa\LaravelBaseRepository\BaseRepository
     */
    public function setModel(string $modelClass): BaseRepository;

    /**
     * Set the repository request to use.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Okipa\LaravelBaseRepository\BaseRepository
     */
    public function setRequest(Request $request): BaseRepository;

    /**
     * Create multiple model instances from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createOrUpdateMultipleFromRequest(
        array $attributesToExcept = [],
        array $attributesToAddOrReplace = []
    ): Collection;

    /**
     * Create one or more model instances from data array.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createOrUpdateMultipleFromArray(array $data): Collection;

    /**
     * Create or update a model instance from data array.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createOrUpdateFromArray(array $data): Model;

    /**
     * Update a model instance from its primary key.
     *
     * @param int   $instancePrimary
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateByPrimary(int $instancePrimary, array $data): Model;

    /**
     * Create or update a model instance from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createOrUpdateFromRequest(
        array $attributesToExcept = [],
        array $attributesToAddOrReplace = []
    ): Model;

    /**
     * Delete a model instance from the request data.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = []);

    /**
     * Delete a model instance from a data array.
     *
     * @param array $data
     *
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteFromArray(array $data): bool;

    /**
     * Delete a model instance from its primary key.
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
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteMultipleFromPrimaries(array $instancePrimaries): int;

    /**
     * Paginate array results.
     *
     * @param array $data
     * @param int   $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateArrayResults(array $data, int $perPage = 20): LengthAwarePaginator;

    /**
     * Find one model instance from its primary key value.
     *
     * @param int  $instancePrimary
     * @param bool $throwsExceptionIfNotFound
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneByPrimary(int $instancePrimary, $throwsExceptionIfNotFound = true);

    /**
     * Find one model instance from an associative array.
     *
     * @param array $data
     * @param bool  $throwsExceptionIfNotFound
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneFromArray(array $data, $throwsExceptionIfNotFound = true);

    /**
     * Find multiple model instance from a « where » parameters array.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findMultipleFromArray(array $data): Collection;

    /**
     * Get all model instances from database.
     *
     * @param array  $columns
     * @param string $orderBy
     * @param string $orderByDirection
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getAll($columns = ['*'], string $orderBy = 'default', string $orderByDirection = 'asc'): Collection;

    /**
     * Instantiate a model instance with an attributes array.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function make(array $data): Model;

    /**
     * Get the model unique storage instance or create one.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function modelUniqueInstance(): Model;
}
