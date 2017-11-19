<?php

namespace Okipa\LaravelCleverBaseRepository\Traits;

use ErrorException;

trait ConfigChecksTrait
{
    /**
     * Check that the repository model has been loaded from database
     *
     * @throws \ErrorException 514
     */
    protected function checkModelDatabaseInstance()
    {
        if (!$this->model || !$this->model->id) {
            throw new ErrorException(get_class($this) . ' : the repository related model "'
                                     . $this->model->getMorphClass() . '" has not been loaded from database. '
                                     . 'Please set a database loaded instance to the repository '
                                     . 'using the "setModel()" method.');
        }
    }

    /**
     * Check that the repository config has been correctly set
     */
    protected function checkRepositoryConfig()
    {
        // we check that the config exists
        if ($this->checkConfigExists()) {
            // we check the configuration json storage instruction
            $this->checkConfigurationJsonStorage();
            // we check the configuration storage path
            $this->checkConfigurationStoragePath();
            // we check the configuration public path
            $this->checkConfigurationPublicPath();
            // we check the configurations validity
            $this->checkConfigurationsValidity();
        }
    }

    /**
     * @return bool
     * @throws \ErrorException
     */
    protected function checkConfigExists()
    {
        $definedConfigKey = !!$this->configKey;
        if ($definedConfigKey && is_null(config($this->configKey))) {
            throw new ErrorException(
                get_class($this) . ' : the config "' . $this->configKey . '" does not exist.'
            );
        }

        return $definedConfigKey;
    }

    /**
     * @throws \ErrorException
     */
    protected function checkConfigurationJsonStorage()
    {
        // we check if the config json storage instruction is defined
        if (!config($this->configKey . '.json_storage')) {
            throw new ErrorException(
                get_class($this) . ' : the config "' . $this->configKey . '" has no defined "json_storage" instruction.'
            );
        }
        // we check if the config json storage instruction is a boolean value
        if (!is_bool($this->configKey . '.json_storage')) {
            throw new ErrorException(
                get_class($this) . ' : the config "' . $this->configKey
                . '" "json_storage" instruction is not a boolean value.'
            );
        }
    }

    /**
     * @throws \ErrorException
     */
    protected function checkConfigurationStoragePath()
    {
        // we check if the config storage path is defined
        if (!config($this->configKey . '.storage_path')) {
            throw new ErrorException(
                get_class($this) . ' : the config "' . $this->configKey . '" has no defined "storage_path" value.'
            );
        }
    }

    /**
     * @throws \ErrorException
     */
    protected function checkConfigurationPublicPath()
    {
        // we check if the config public path is defined
        if (!config($this->configKey . '.public_path')) {
            throw new ErrorException(
                get_class($this) . ' : the config "' . $this->configKey . '" has no defined public_path" value.'
            );
        }
    }

    /**
     * @param void
     *
     * @return void
     */
    protected function checkConfigurationsValidity()
    {
        // we get the repository file types
        $cfgTypes = array_filter(config($this->configKey), function($key) {
            return in_array($key, $this->fileTypes);
        }, ARRAY_FILTER_USE_KEY);
        // we check each file type config
        foreach ($cfgTypes as $cfgTypeKey => $cfgTypeContent) {
            foreach ($cfgTypeContent as $cfgTypeContentKey => $cfgTypeContentValues) {
                $cfgPath = $this->configKey . '.' . $cfgTypeKey . '.' . $cfgTypeContentKey;
                $this->checkConfigurationName($cfgTypeContentValues, $cfgPath);
                $this->checkConfigurationAuthorizedExtensions($cfgTypeContentValues, $cfgPath);
                if ($cfgTypeKey === 'image') {
                    $this->checkImageConfigurationAvailableSizes($cfgTypeContentValues, $cfgPath);
                }
            }
        }
    }

    /**
     * @param array  $configuration
     * @param string $configPath
     *
     * @throws \ErrorException
     */
    protected function checkConfigurationName(array $configuration, string $configPath)
    {
        if (empty($configuration['name'])) {
            throw new ErrorException(
                get_class($this) . ' : the config "' . $configPath . '" as no defined "name" value.'
            );
        }
    }

    /**
     * @param array  $configuration
     * @param string $configPath
     *
     * @throws \ErrorException
     */
    protected function checkConfigurationAuthorizedExtensions(array $configuration, string $configPath)
    {
        // we check that the given configuration has some defined authorized extensions
        if (empty($configuration['authorized_extensions'])) {
            throw new ErrorException(
                'The config "' . $configPath . '" has no defined "authorized_extensions" value.'
            );
        };
        // we check that the authorized extensions value is an array
        if (!is_array($configuration['authorized_extensions'])) {
            throw new ErrorException(
                'The "authorized_extensions" value for the config "' . $configPath . '" is not an array.'
            );
        }
    }

    /**
     * @param array  $configuration
     * @param string $configPath
     *
     * @throws \ErrorException
     */
    protected function checkImageConfigurationAvailableSizes(array $configuration, string $configPath)
    {
        // we check that the given configuration has some defined available sizes
        if (empty($configuration['available_sizes'])) {
            throw new ErrorException(
                get_class($this) . ' : the config "' . $configPath . '" has no defined "available_sizes" value.'
            );
        };
        // we check that the available sizes value is an array
        if (!is_array($configuration['available_sizes'])) {
            throw new ErrorException(
                get_class($this) . ' : the "available_sizes" value for the config "' . $configPath
                . '" is not an array.'
            );
        }
        // we check each size validity
        foreach ($configuration['available_sizes'] as $sizeKey => $sizeValues) {
            // we check that the size is not empty
            if (empty($sizeValues) || sizeof($sizeValues) !== 2) {
                throw new ErrorException(
                    get_class($this) . ' : incorrect "' . $sizeKey . '" size value : '
                    . json_encode($sizeValues) . ' for the config "' . $configPath . '".'
                    . 'Each given size array must contain two values : [\'width\' => (int), \'height\' => (int)] '
                    . '(one of them can be null)'
                );
            }
            // we get the size width and height
            if (empty($sizeValues['width'])) {
                throw new ErrorException(
                    get_class($this) . ' : the config "' . $configPath . '.available_sizes.' . $sizeKey
                    . '" has no defined "width" value.'
                );
            }
            if (empty($sizeValues['height'])) {
                throw new ErrorException(
                    get_class($this) . ' : the config "' . $configPath . '.available_sizes.' . $sizeKey
                    . '" has no defined "height" value.'
                );
            }
            $width = $sizeValues['width'];
            $height = $sizeValues['height'];
            if (!$width && !$height) {
                throw new ErrorException(
                    get_class($this) . ' : incorrect "' . $sizeKey . '" size value : '
                    . json_encode($sizeValues) . '.' . ' for the config "' . $configPath . '".'
                    . 'Both width and height are null.'
                );
            }
        }
    }

    /**
     * @throws \ErrorException
     */
    protected function checkConfigFileTypes()
    {
        if (empty(config($this->configKey . '.file_types')) && !is_array(config($this->configKey . '.file_types'))) {
            throw new ErrorException(
                get_class($this) . ' : the "repository" config has no defined "file_types" array.'
            );
        }
    }
}