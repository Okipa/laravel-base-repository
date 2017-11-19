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
     * @param string|null $path
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
     * @param string|null $path
     * @param bool        $absolute
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

    public function createEntity(Request $request, array $except = [])
    {
        // we get the attributes from the request
        $attributes = $request->except(array_merge($except, $this->getDefaultRequestExceptEntries()));
        // we create the entity
        if ($this->jsonStorage) {
            $this->storeAttributesToJson($attributes);
        } else {
            $this->checkModelDatabaseInstance();
            $this->create($attributes);
        }

        foreach ($this->getAvailableImageKeys() as $imageKey) {
            $image = $request->file($imageKey);
            $removeImageOrder = 'remove_' . $imageKey;
        }
    }

    protected function getDefaultRequestExceptEntries()
    {
        $defaultRequestEnties = ['_token', '_method'];
        foreach ($this->getAvailableImageKeys() as $imageKey) {
            $defaultRequestEnties[] = 'remove_' . $imageKey;
        }
    }

    public function updateEntity(Request $request, array $except = [])
    {
    }

    public function destroyEntity(Request $request)
    {
    }
}