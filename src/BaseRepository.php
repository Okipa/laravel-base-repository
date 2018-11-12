<?php

namespace Okipa\LaravelBaseRepository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The repository associated main model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;
    /**
     * The repository associated request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;
    /**
     * Default attributes to automatically except from request treatments.
     *
     * @var array
     */
    protected $defaultAttributesToExcept = ['_token', '_method'];
    /**
     * Automatically except defined $defaultAttributesToExcept from the request treatments.
     *
     * @var boolean
     */
    protected $exceptDefaultAttributes = true;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        if ($this->model) {
            $this->setModel($this->model);
        }
        $this->setRequest(request());
    }

    /**
     * Set the repository request to use.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Okipa\LaravelBaseRepository\BaseRepository
     */
    public function setRequest(Request $request): BaseRepository
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Create multiple model instances from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToAddOrReplace (dot notation accepted)
     * @param array $attributesToExcept       (dot notation accepted)
     * @param bool  $saveMissingModelFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createOrUpdateMultipleFromRequest(
        array $attributesToAddOrReplace = [],
        array $attributesToExcept = [],
        bool $saveMissingModelFillableAttributesToNull = true
    ): Collection {
        $this->exceptAttributesFromRequest($attributesToExcept);
        $this->addOrReplaceAttributesInRequest($attributesToAddOrReplace);

        return $this->createOrUpdateMultipleFromArray($this->request->all(), $saveMissingModelFillableAttributesToNull);
    }

    /**
     * Except attributes from request.
     *
     * @param array $attributesToExcept (dot notation accepted)
     *
     * @return void
     */
    protected function exceptAttributesFromRequest(array $attributesToExcept = [])
    {
        if ($this->exceptDefaultAttributes) {
            $attributesToExcept = array_merge($this->defaultAttributesToExcept, $attributesToExcept);
        }
        $this->request->replace($this->request->except($attributesToExcept));
    }

    /**
     * Add or replace attributes in request.
     *
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return void
     */
    protected function addOrReplaceAttributesInRequest(array $attributesToAddOrReplace = [])
    {
        $attributesToAddOrReplaceArray = [];
        foreach ($attributesToAddOrReplace as $key => $value) {
            array_set($attributesToAddOrReplaceArray, $key, $value);
        }
        $newRequestAttributes = array_replace_recursive($this->request->all(), $attributesToAddOrReplaceArray);
        $this->request->replace($newRequestAttributes);
    }

    /**
     * Create one or more model instances from data array.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     * @param bool  $saveMissingModelFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createOrUpdateMultipleFromArray(
        array $data,
        bool $saveMissingModelFillableAttributesToNull = true
    ): Collection {
        $models = new Collection();
        foreach ($data as $instanceData) {
            $models->push($this->createOrUpdateFromArray($instanceData, $saveMissingModelFillableAttributesToNull));
        }

        return $models;
    }

    /**
     * Create or update a model instance from data array.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     * @param bool  $saveMissingModelFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdateFromArray(array $data, bool $saveMissingModelFillableAttributesToNull = true): Model
    {
        $primary = $this->getModelPrimaryFromArray($data);

        return $primary
            ? $this->updateByPrimary($primary, $data, $saveMissingModelFillableAttributesToNull)
            : $this->getModel()->create($data);
    }

    /**
     * Get model primary value from a data array.
     *
     * @param array $data
     *
     * @return mixed
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function getModelPrimaryFromArray(array $data)
    {
        return array_get($data, $this->getModel()->getKeyName());
    }

    /**
     * Get the repository model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function getModel(): Model
    {
        if ($this->model instanceof Model) {
            return $this->model;
        }
        throw new ModelNotFoundException(
            'You must declare your repository $model attribute with an Illuminate\Database\Eloquent\Model '
            . 'namespace to use this feature.'
        );
    }

    /**
     * Set the repository model class to instantiate.
     *
     * @param string $modelClass
     *
     * @return \Okipa\LaravelBaseRepository\BaseRepository
     */
    public function setModel(string $modelClass): BaseRepository
    {
        $this->model = app($modelClass);

        return $this;
    }

    /**
     * Update a model instance from its primary key.
     *
     * @param int   $primary
     * @param array $data
     * @param bool  $saveMissingModelFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateByPrimary(
        int $primary,
        array $data,
        bool $saveMissingModelFillableAttributesToNull = true
    ): Model {
        $instance = $this->getModel()->findOrFail($primary);
        $data = $saveMissingModelFillableAttributesToNull ? $this->setMissingFillableAttributesToNull($data) : $data;
        $instance->update($data);

        return $instance;
    }

    /**
     * Add the missing model fillable attributes with a null value.
     *
     * @param array $data
     *
     * @return array
     */
    public function setMissingFillableAttributesToNull(array $data): array
    {
        $fillableAttributes = $this->getModel()->getFillable();
        $dataWithMissingAttributesToNull = [];
        foreach ($fillableAttributes as $fillableAttribute) {
            $dataWithMissingAttributesToNull[$fillableAttribute] =
                isset($data[$fillableAttribute]) ? $data[$fillableAttribute] : null;
        }

        return $dataWithMissingAttributesToNull;
    }

    /**
     * Create or update a model instance from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToAddOrReplace (dot notation accepted)
     * @param array $attributesToExcept       (dot notation accepted)
     * @param bool  $saveMissingModelFillableAttributesToNull
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createOrUpdateFromRequest(
        array $attributesToAddOrReplace = [],
        array $attributesToExcept = [],
        bool $saveMissingModelFillableAttributesToNull = true
    ): Model {
        $this->exceptAttributesFromRequest($attributesToExcept);
        $this->addOrReplaceAttributesInRequest($attributesToAddOrReplace);

        return $this->createOrUpdateFromArray($this->request->all(), $saveMissingModelFillableAttributesToNull);
    }

    /**
     * Delete a model instance from the request data.
     *
     * @param array $attributesToAddOrReplace (dot notation accepted)
     * @param array $attributesToExcept       (dot notation accepted)
     *
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteFromRequest(array $attributesToAddOrReplace = [], array $attributesToExcept = [])
    {
        $this->exceptAttributesFromRequest($attributesToExcept);
        $this->addOrReplaceAttributesInRequest($attributesToAddOrReplace);

        return $this->deleteFromArray($this->request->all());
    }

    /**
     * Delete a model instance from a data array.
     *
     * @param array $data
     *
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteFromArray(array $data): bool
    {
        $primary = $this->getModelPrimaryFromArray($data);

        return $this->getModel()->findOrFail($primary)->delete();
    }

    /**
     * Delete a model instance from its primary key.
     *
     * @param int $primary
     *
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteByPrimary(int $primary)
    {
        return $this->getModel()->findOrFail($primary)->delete();
    }

    /**
     * Delete multiple model instances from their primary keys.
     *
     * @param array $instancePrimaries
     *
     * @return int
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteMultipleFromPrimaries(array $instancePrimaries): int
    {
        return $this->getModel()->destroy($instancePrimaries);
    }

    /**
     * Paginate array results.
     *
     * @param array $data
     * @param int   $perPage
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateArrayResults(array $data, int $perPage = 20): LengthAwarePaginator
    {
        $page = $this->request->input('page', 1);
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(
            array_slice($data, $offset, $perPage, false),
            count($data),
            $perPage,
            $page,
            [
                'path'  => $this->request->url(),
                'query' => $this->request->query(),
            ]
        );
    }

    /**
     * Find one model instance from its primary key value.
     *
     * @param int  $primary
     * @param bool $throwsExceptionIfNotFound
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneByPrimary(int $primary, $throwsExceptionIfNotFound = true)
    {
        return $throwsExceptionIfNotFound
            ? $this->getModel()->findOrFail($primary)
            : $this->getModel()->find($primary);
    }

    /**
     * Find one model instance from an associative array.
     *
     * @param array $data
     * @param bool  $throwsExceptionIfNotFound
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneFromArray(array $data, $throwsExceptionIfNotFound = true)
    {
        return $throwsExceptionIfNotFound
            ? $this->getModel()->where($data)->firstOrFail()
            : $this->getModel()->where($data)->first();
    }

    /**
     * Find multiple model instances from a « where » parameters array.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findMultipleFromArray(array $data): Collection
    {
        return $this->getModel()->where($data)->get();
    }

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
    public function getAll($columns = ['*'], string $orderBy = 'default', string $orderByDirection = 'asc'): Collection
    {
        $orderBy = $orderBy === 'default' ? $this->getModel()->getKeyName() : $orderBy;

        return $this->getModel()->orderBy($orderBy, $orderByDirection)->get($columns);
    }

    /**
     * Instantiate a model instance with an attributes array.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function make(array $data): Model
    {
        return app($this->getModel()->getMorphClass())->fill($data);
    }

    /**
     * Get the model unique storage instance or create one.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function modelUniqueInstance(): Model
    {
        $modelInstance = $this->getModel()->first();
        if (! $modelInstance) {
            $modelInstance = $this->getModel()->create([]);
        }

        return $modelInstance;
    }

    /**
     * Find multiple model instances from an array of ids.
     *
     * @param array $primaries
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMultipleFromPrimaries(array $primaries): Collection
    {
        return $this->getModel()->findMany($primaries);
    }
}
