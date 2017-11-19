<?php

namespace Okipa\LaravelCleverBaseRepository\Traits;

use Log;

trait JsonManagerTrait
{
    /**
     * The repository json content
     *
     * @var array
     */
    protected $jsonContent;

    /**
     * Get the given attribute (translated or not) value from the repository json file
     *
     * @param string      $key
     * @param string|null $localeCode
     *
     * @return string|null
     */
    public function getAttributeFromJson(string $key, string $localeCode = null)
    {
        // we get the locale code
        if (!$localeCode) {
            $localeCode = config('app.locale');
        }
        // we get the json content
        $jsonContent = $this->getAttributesFromJson();
        // we get the translated attribute
        $attribute = !empty($jsonContent[$key])
            ? $jsonContent[$key]
            : null;
        // if the attribute is an array which contains the locale code
        if (is_array($attribute) && array_key_exists($localeCode, $attribute)) {
            return $attribute[$localeCode];
        }

        return $attribute;
    }

    /**
     * Get the raw attributes from the repository json file
     *
     * @param bool $forceRefresh
     *
     * @return array
     * @internal param $void
     */
    public function getAttributesFromJson(bool $forceRefresh = false)
    {
        if (!$this->jsonContent || $forceRefresh) {
            // we load the json content
            $this->loadJsonContent();
        }

        return $this->jsonContent;
    }

    /**
     * Load the repository json content
     */
    protected function loadJsonContent()
    {
        if (is_file($this->getStoragePath('attributes.json'))) {
            $this->jsonContent = json_decode(file_get_contents($this->getStoragePath('attributes.json')), true);
        } else {
            $this->jsonContent = [];
            Log::info('The file "' . $this->getStoragePath('attributes.json') . '" does not exist.');
        }
    }

    /**
     * Store the given attributes to the repository json file
     *
     * @param array $attributes
     *
     * @return void
     */
    public function storeAttributesToJson(array $attributes)
    {
        // we get the attributes currently stored in the json file
        $jsonAttributes = $this->getAttributesFromJson(true);
        // we replace the given key / values
        $attributesToStore = array_replace_recursive($jsonAttributes, $attributes);
        // we store the attributes in the content json file
        file_put_contents(
            $this->getStoragePath('attributes.json'),
            json_encode($attributesToStore)
        );
    }
}