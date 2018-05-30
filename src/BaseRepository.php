<?php

namespace Okipa\LaravelBaseRepository;

use Exception;
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
        if ($this->model) {
            $this->setModel($this->model);
        }
        $this->setRequest(request());
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function createMultipleFromArray(array $data)
    {
        $models = new Collection();
        foreach ($data as $instanceData) {
            $models->push($this->getModel()->create($instanceData));
        }

        return $models;
    }

    /**
     * Get the repository model.
     *
     * @return \Exception|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    protected function getModel()
    {
        if ($this->model instanceof Model) {
            return $this->model;
        }
        throw new Exception('You must declare your repository $model attribute with an Illuminate\Database\Eloquent\Model namespace to use this feature.');
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
            ? $this->updateByPrimary($primary, $data)
            : $this->getModel()->create($data);
    }

    /**
     * Get model primary value from a data array.
     *
     * @param array $data
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getModelPrimaryFromArray(array $data)
    {
        return array_get($data, $this->getModel()->getKeyName());
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
    public function updateByPrimary(int $instancePrimary, array $data)
    {
        $instance = $this->getModel()->findOrFail($instancePrimary);
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function deleteFromArray(array $data)
    {
        $primary = $this->getModelPrimaryFromArray($data);

        return $this->getModel()->findOrFail($primary)->delete();
    }

    /**
     * Delete a model instance from its primary key.
     *
     * @param int $instancePrimary
     *
     * @return bool|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function deleteByPrimary(int $instancePrimary)
    {
        return $this->getModel()->findOrFail($instancePrimary)->delete();
    }

    /**
     * Delete multiple model instances from their primary keys.
     *
     * @param array $instancePrimaries
     *
     * @return int
     * @throws \Exception
     */
    public function deleteMultipleFromPrimaries(array $instancePrimaries)
    {
        return $this->getModel()->destroy($instancePrimaries);
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
     * @param int  $instancePrimary
     * @param bool $throwsExceptionIfNotFound
     *
     * @return mixed
     * @throws \Exception
     */
    public function findOneByPrimary(int $instancePrimary, $throwsExceptionIfNotFound = true)
    {
        return $throwsExceptionIfNotFound
            ? $this->getModel()->findOrFail($instancePrimary)
            : $this->getModel()->find($instancePrimary);
    }

    /**
     * Find one model instance from an associative array.
     *
     * @param array $data
     * @param bool  $throwsExceptionIfNotFound
     *
     * @return mixed
     * @throws \Exception
     */
    public function findOneFromArray(array $data, $throwsExceptionIfNotFound = true)
    {
        return $throwsExceptionIfNotFound
            ? $this->getModel()->where($data)->firstOrFail()
            : $this->getModel()->where($data)->first();
    }

    /**
     * Find multiple model instance from a « where » parameters array.
     *
     * @param array $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function findMultipleFromArray(array $data)
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
     * @return mixed
     * @throws \Exception
     */
    public function getAll($columns = ['*'], string $orderBy = 'default', string $orderByDirection = 'asc')
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
     * @throws \Exception
     */
    public function make(array $data)
    {
        return app($this->getModel()->getMorphClass())->fill($data);
    }
}
