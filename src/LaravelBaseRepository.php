<?php

namespace Okipa\LaravelBaseRepository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LaravelBaseRepository
{
    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        // we set the repository model
        if ($this->model && !$this->model instanceof Model) {
            $this->model = app($this->model);
        }
    }

    /**
     * Automatically save the entity attributes and its related files & images from the request and according to the
     * repository configuration.
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $except       The request keys that will not be stored
     * @param array                    $customValues The key / values couples that will override the request data
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Foundation\Application|\Illuminate\Support\Collection|mixed
     * @throws \ErrorException
     */
    public function saveEntity(Request $request, array $except = [], array $customValues = [])
    {
        // we get the attributes from the request
        $attributes = $this->exceptAttributesFromRequest($request, $except);
        // we merge the custom values to the attributes
        $attributes = array_merge_recursive($attributes, $customValues);
        // we create the entity
        if ($this->jsonStorage) {
            $this->storeAttributesToJson($attributes);
        } else {
            $this->model = $this->model->id ? $this->updateById($this->model, $attributes) : $this->create($attributes);
        }
        // we store the images
        foreach ($this->getAvailableImageKeys() as $imageKey) {
            $this->storeImageFromUploadedFile($imageKey, $request->file($imageKey), $request->{'remove_' . $imageKey});
        }

        return $this->jsonStorage ? collect($this->getAttributesFromJson(true)) : $this->model;
    }

    /**
     * Destroy the current model entity
     *
     * @return void
     */
    public function destroyEntity()
    {
        $this->model->delete();
    }

    /**
     * Except attributes from request
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $except
     *
     * @return array $defaultRequestEntries
     */
    protected function exceptLaravelHttpAttributesFromRequest(Request $request, array $except = [])
    {
        $except[] = '_token';
        $except[] = '_method';

        return $request->except($except);
    }
}
