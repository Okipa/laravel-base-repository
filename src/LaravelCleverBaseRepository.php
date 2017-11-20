<?php

namespace Okipa\LaravelCleverBaseRepository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Okipa\LaravelCleverBaseRepository\Traits\EloquentOverlayTrait;
use Okipa\LaravelCleverBaseRepository\Traits\ImageManagerTrait;
use Okipa\LaravelCleverBaseRepository\Traits\JsonManagerTrait;
use Okipa\LaravelCleverBaseRepository\Traits\RepositoryAttributesTrait;

class LaravelCleverBaseRepository
{
    use RepositoryAttributesTrait;
    use EloquentOverlayTrait;
    use ImageManagerTrait;
    use JsonManagerTrait;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        // we set the config key
        $this->configKey = $this->configKey ? 'repository.' . $this->configKey : null;
        // we check the repository config
        $this->setRepositoryAttributesFromConfig();
        // we set the repository model
        if ($this->model && !$this->model instanceof Model) {
            $this->model = app($this->model);
        }
    }

    /**
     * Get the repository storage path
     *
     * @param string|null $path The path we want to add after the returned storage path
     *
     * @return string
     */
    public function getStoragePath(string $path = null)
    {
        return storage_path($this->storagePath) . ($path ? '/' . $path : '');
    }

    /**
     * Get the repository public path
     *
     * @param string|null $path     The path we want to add after the returned public path
     * @param bool        $absolute Whether we want the returned path to be absolute
     *
     * @return string
     * @throws \ErrorException
     */
    public function getPublicPath(string $path = null, bool $absolute = false)
    {
        return $absolute
            ? public_path($this->publicPath) . ($path ? '/' . $path : '')
            : $this->publicPath . ($path ? '/' . $path : '');
    }

    /**
     * Automatically save the entity attributes and its related images from the request and according to the repository
     * configuration.
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $except       The request keys that will not be stored
     * @param array                    $customValues The key / values couples that will override the request data
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Foundation\Application|\Illuminate\Support\Collection|mixed
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
     * Except attributes from request
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $except
     *
     * @return array $defaultRequestEntries
     */
    protected function exceptAttributesFromRequest(Request $request, array $except)
    {
        $except[] = '_token';
        $except[] = '_method';
        foreach ($this->getAvailableImageKeys() as $imageKey) {
            $except[] = $imageKey;
            $except[] = 'remove_' . $imageKey;
        }

        return $request->except($except);
    }

    /**
     * Destroy the current model entity and all its related images defined in the repository configuration
     *
     * @return void
     */
    public function destroyEntity()
    {
        // we check that the current repository model is loaded from database
        $this->checkModelDatabaseInstance();
        // we destroy the entity images
        foreach ($this->getAvailableImageKeys() as $imageKey) {
            if ($imageName = $this->model->{$imageKey}) {
                $this->destroyImage($imageKey, $imageName);
            }
        }
        // we destroy the entity
        $this->model->delete();
    }
}