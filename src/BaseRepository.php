<?php

namespace Okipa\LaravelBaseRepository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $this->setModel($this->model);
        $this->setRequest(request());
    }

    /**
     * Set the repository model class to instantiate.
     *
     * @param string $modelClass
     *
     * @return $this
     */
    public function setModel(string $modelClass)
    {
        $this->model = app($modelClass);

        return $this;
    }

    /**
     * Set the repository request to use.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Create multiple model instances from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultipleFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = [])
    {
        $this->exceptAttributesFromRequest($attributesToExcept);
        $this->addOrReplaceAttributesInRequest($attributesToAddOrReplace);

        return $this->createMultipleFromArray($this->request->all());
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
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultipleFromArray(array $data)
    {
        $models = new Collection();
        foreach ($data as $instanceData) {
            $models->push($this->model->create($instanceData));
        }

        return $models;
    }

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
    public function createOrUpdateFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = [])
    {
        $this->exceptAttributesFromRequest($attributesToExcept);
        $this->addOrReplaceAttributesInRequest($attributesToAddOrReplace);

        return $this->createOrUpdateFromArray($this->request->all());
    }

    /**
     * Create or update a model instance from data array.
     * The use of this method suppose that your array is correctly formatted.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function createOrUpdateFromArray(array $data)
    {
        $primary = $this->getModelPrimaryFromArray($data);

        return $primary
            ? $this->updateFromPrimary($primary, $data)
            : $this->model->create($data);
    }

    /**
     * Get model primary value from a data array.
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function getModelPrimaryFromArray(array $data)
    {
        return array_get($data, $this->model->getKeyName());
    }

    /**
     * Update a model instance from its primary key.
     *
     * @param int   $instancePrimary
     * @param array $data
     *
     * @return Model
     * @throws \Exception
     */
    public function updateFromPrimary(int $instancePrimary, array $data)
    {
        $instance = $this->model->findOrFail($instancePrimary);
        $instance->update($data);
        
        return $instance->fresh();
    }

    /**
     * Delete a model instance from the request data.
     *
     * @param array $attributesToExcept       (dot notation accepted)
     * @param array $attributesToAddOrReplace (dot notation accepted)
     *
     * @return bool|null
     */
    public function deleteFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = [])
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
    public function deleteFromArray(array $data)
    {
        $primary = $this->getModelPrimaryFromArray($data);

        return $this->model->findOrFail($primary)->delete();
    }

    /**
     * Delete a model instance from its primary key.
     *
     * @param int $instancePrimary
     *
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteFromPrimary(int $instancePrimary)
    {
        return $this->model->findOrFail($instancePrimary)->delete();
    }

    /**
     * Delete multiple model instances from their primary keys.
     *
     * @param array $instancePrimaries
     *
     * @return int
     */
    public function deleteMultipleFromPrimaries(array $instancePrimaries)
    {
        return $this->model->destroy($instancePrimaries);
    }

    /**
     * Paginate array results.
     *
     * @param array $data
     * @param int   $perPage
     *
     * @return LengthAwarePaginator
     */
    public function paginateArrayResults(array $data, int $perPage = 20)
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
     * @param int $instancePrimary
     *
     * @return mixed
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneFromPrimary(int $instancePrimary)
    {
        return $this->model->findOrFail($instancePrimary);
    }

    /**
     * Find one model instance from an associative array.
     *
     * @param array $data
     *
     * @return mixed
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneFromArray(array $data)
    {
        return $this->model->where($data)->firstOrFail();
    }

    /**
     * Find multiple model instance from a « where » parameters array.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function findMultipleFromArray(array $data)
    {
        return $this->model->where($data)->get();
    }

    /**
     * Get all model instances from database.
     *
     * @param array  $columns
     * @param string $orderBy
     * @param string $orderByDirection
     *
     * @return mixed
     */
    public function getAll($columns = ['*'], string $orderBy = 'default', string $orderByDirection = 'asc')
    {
        $orderBy = $orderBy === 'default' ? $this->model->getKeyName() : $orderBy;

        return $this->model->orderBy($orderBy, $orderByDirection)->get($columns);
    }
}
