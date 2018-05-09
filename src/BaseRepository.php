<?php

namespace Okipa\LaravelBaseRepository;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseRepository
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
     * Set the repository request to use.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
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
     * Create one or more model instances from the request data.
     * The use of this method suppose that your request is correctly formatted.
     * If not, you can use the $exceptFromSaving and $addToSaving attributes to do so.
     *
     * @param array $attributesToExcept
     * @param array $attributesToAddOrReplace
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
     * @param array $attributesToExcept
     * @param array $attributesToAddOrReplace
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function createOrUpdateFromRequest(array $attributesToExcept = [], array $attributesToAddOrReplace = [])
    {
        $this->exceptAttributesFromRequest($attributesToExcept);
        $this->addOrReplaceAttributesInRequest($attributesToAddOrReplace);
        $primary = $this->getModelPrimaryFromRequest();

        return $primary
            ? $this->updateFromPrimary($primary, $this->request->all())
            : $this->model->create($this->request->all());
    }

    /**
     * Get model primary value from request.
     *
     * @return int
     */
    protected function getModelPrimaryFromRequest()
    {
        return $this->request->input($this->model->getKeyName());
    }

    /**
     * Update a model instance from its primary key
     *
     * @param int   $instancePrimary
     * @param array $data
     *
     * @return Model
     * @throws \Exception
     */
    public function updateFromPrimary(int $instancePrimary, array $data)
    {
        $this->model->findOrFail($instancePrimary)->update($data);
        
        return $this->model->find($instancePrimary);
    }

    /**
     * Destroy a model instance from the request data
     *
     * @return bool|null|\Exception
     * @throws \Exception
     */
    public function deleteFromRequest()
    {
        $primary = $this->getModelPrimaryFromRequest();

        return $primary
            ? $this->model->findOrFail($primary)->delete()
            : new Exception('The request does not contain the repository-associated-model primary key value.');
    }

    /**
     * Delete a model instance from its primary key
     *
     * @param int $instancePrimary
     *
     * @return bool|null
     * @throws \Exception
     */
    public function deleteFromPrimary(int $instancePrimary)
    {
        return $this->model->findOrFail($instancePrimary)->delete();
    }

    /**
     * Delete multiple model instances from their primary keys
     *
     * @param array $instancePrimaries
     *
     * @return int
     */
    public function deleteMultipleFromPrimaries(array $instancePrimaries)
    {
        return $this->model->destroy($instancePrimaries);
    }
}
