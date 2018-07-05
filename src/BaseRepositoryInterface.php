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
     * @param array $attributesToAddOrReplace (dot notation accepted)
     * @param array $attributesToExcept       (dot notation accepted)
     * @param bool  $missingFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createOrUpdateMultipleFromRequest(
        array $attributesToAddOrReplace = [],
        array $attributesToExcept = [],
        bool $missingFillableAttributesToNull = true
    ): Collection;

    /**
     * Create one or more model instances from data array.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     * @param bool  $missingFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createOrUpdateMultipleFromArray(
        array $data,
        bool $missingFillableAttributesToNull = true
    ): Collection;

    /**
     * Create or update a model instance from data array.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     * @param bool  $missingFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdateFromArray(array $data, bool $missingFillableAttributesToNull = true): Model;

    /**
     * Update a model instance from its primary key.
     *
     * @param int   $instancePrimary
     * @param array $data
     * @param bool  $missingFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateByPrimary(
        int $instancePrimary,
        array $data,
        bool $missingFillableAttributesToNull = true
    ): Model;

    /**
     * Create or update a model instance from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToAddOrReplace (dot notation accepted)
     * @param array $attributesToExcept       (dot notation accepted)
     * @param bool  $missingFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdateFromRequest(
        array $attributesToAddOrReplace = [],
        array $attributesToExcept = [],
        bool $missingFillableAttributesToNull = true
    ): Model;

    /**
     * Delete a model instance from the request data.
     *
     * @param array $attributesToAddOrReplace (dot notation accepted)
     * @param array $attributesToExcept       (dot notation accepted)
     *
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteFromRequest(array $attributesToAddOrReplace = [], array $attributesToExcept = []);

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
     * Find multiple model instances from a « where » parameters array.
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

    /**
     * Add the missing model fillable attributes with a null value.
     *
     * @param array $data
     *
     * @return array
     */
    public function setMissingFillableAttributesToNull(array $data): array;

    /**
     * Find multiple model instances from an array of ids.
     *
     * @param array $ids
     */
    public function findMultipleFromIds(array $ids);
}
