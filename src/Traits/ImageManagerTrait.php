<?php

namespace Okipa\LaravelCleverBaseRepository\Traits;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Spatie\ImageOptimizer\OptimizerChain;

trait ImageManagerTrait
{
    /**
     * Get the image size width
     *
     * @param string $imageKey
     * @param string $sizeKey
     *
     * @return int
     */
    public function getImageSizeWidth(string $imageKey, string $sizeKey)
    {
        // we get the image config size
        $size = $this->getImageConfigSize($imageKey, $sizeKey);

        return $size['width'];
    }

    /**
     * @param string $imageKey
     * @param string $sizeKey
     *
     * @return array
     */
    protected function getImageConfigSize(string $imageKey, string $sizeKey)
    {
        // we get the image config
        $imageConfig = $this->getImageConfig($imageKey);
        // we check that the given size key exists
        if (!array_key_exists($sizeKey, $imageConfig['available_sizes'])) {
            throw new InvalidArgumentException(get_class($this) . ' : the size key "' . $sizeKey
                                               . '" does not exist in the "' . $this->configKey . '.images.' . $imageKey
                                               . '" configuration.');
        }

        return $imageConfig['available_sizes'][$sizeKey];
    }

    /**
     * @param string $imageKey
     *
     * @return array
     */
    protected function getImageConfig(string $imageKey)
    {
        // we get the config
        $imageConfig = config($this->configKey . '.images');
        // we check that the given key exists
        if (!array_key_exists($imageKey, $imageConfig)) {
            throw new InvalidArgumentException(get_class($this) . ' : the image key "' . $imageKey
                                               . '" does not exist in the "' . $this->configKey . '.images'
                                               . '" configuration.');
        }

        return $imageConfig[$imageKey];
    }

    /**
     * @return array
     */
    protected function getAvailableImageKeys()
    {
        return array_keys(config($this->configKey . '.images'));
    }

    /**
     * @param string $imageKey
     *
     * @return array
     */
    protected function getImageAvailableSizes(string $imageKey)
    {
        // we get the image config
        $imageConfig = $this->getImageConfig($imageKey);

        return $imageConfig['available_sizes'];
    }

    /**
     * Get the image size height
     *
     * @param string $imageKey
     * @param string $sizeKey
     *
     * @return int
     */
    public function getImageSizeHeight(string $imageKey, string $sizeKey)
    {
        // we get the image config size
        $size = $this->getImageConfigSize($imageKey, $sizeKey);

        return $size['height'];
    }

    /**
     * Get the maximum width from the image sizes
     *
     * @param string $imageKey
     *
     * @return int
     */
    public function getImageMaxWidth(string $imageKey)
    {
        // we get the image config
        $availableSizes = $this->getImageAvailableSizes($imageKey);
        // we set the default bigger width key
        $biggerWidthKey = array_first(array_keys($availableSizes));
        foreach ($availableSizes as $currentSizeKey => $currentSize) {
            $currentSizeWidth = array_first($currentSize);
            $biggerSizeWidth = array_first($availableSizes[$biggerWidthKey]);
            $biggerWidthKey = $currentSizeWidth > $biggerSizeWidth ? $currentSizeKey : $biggerWidthKey;
        }

        return array_first($availableSizes[$biggerWidthKey]);
    }

    /**
     * * Get the maximum height from the image sizes
     *
     * @param string $imageKey
     *
     * @return int
     */
    public function getImageMaxHeight(string $imageKey)
    {
        // we get the image config
        $availableSizes = $this->getImageAvailableSizes($imageKey);
        // we set the default bigger width key
        $biggerWidthKey = array_first(array_keys($availableSizes));
        foreach ($availableSizes as $currentSizeKey => $currentSize) {
            $currentSizeWidth = array_last($currentSize);
            $biggerSizeWidth = array_last($availableSizes[$biggerWidthKey]);
            $biggerWidthKey = $currentSizeWidth > $biggerSizeWidth ? $currentSizeKey : $biggerWidthKey;
        }

        return array_last($availableSizes[$biggerWidthKey]);
    }

    /**
     * Store the given uploaded image, according to the configuration available sizes
     *
     * @param string                             $imageKey
     * @param \Illuminate\Http\UploadedFile|null $uploadedImage
     * @param bool                               $removeImage
     *
     * @return void
     */
    public function storeImageFromUploadedFile(
        string $imageKey,
        UploadedFile $uploadedImage = null,
        bool $removeImage = false
    ) {
        $generatedImageName = null;
        // we remove the current image
        if ($uploadedImage || $removeImage) {
            if ($currentImageName = $this->getCurrentImageName($imageKey)) {
                $this->destroyImage($imageKey, $currentImageName);
            }
        }
        // we store the image
        if ($uploadedImage) {
            $generatedImageName = $this->storeImageProcess(
                $imageKey,
                $uploadedImage->getRealPath(),
                $uploadedImage->getClientOriginalExtension(),
                true
            );
        } elseif ($removeImage) {
            $generatedImageName = $this->storeImageProcess(
                $imageKey,
                database_path('seeds/files/default/image.jpg'),
                'jpg',
                false
            );
        }
        // we save the image name
        if ($this->checkModelDatabaseInstance(false)) {
            $this->model->update([
                $imageKey => $generatedImageName,
            ]);
        } else {
            $this->storeAttributesToJson([
                $imageKey => $generatedImageName,
            ]);
        }
    }

    /**
     * @param string $imageKey
     *
     * @return mixed|null|string
     */
    protected function getCurrentImageName(string $imageKey)
    {
        return $this->model && $this->model->{$imageKey}
            ? $this->model->{$imageKey}
            : $this->getAttributeFromJson($imageKey);
    }

    /**
     * Destroy all the images, according to the configuration available sizes
     *
     * @param string $imageKey
     * @param string $imageName
     *
     * @throws \ErrorException
     */
    public function destroyImage(string $imageKey, string $imageName)
    {
        // we get the image extension
        if (!$extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION))) {
            throw new InvalidArgumentException(get_class($this) . ' : the given image "' . $imageName
                                               . '" has no extension.');
        };
        // we get the image name without its extension
        $imgNameWithoutExt = str_slug(pathinfo($imageName, PATHINFO_FILENAME));
        // we get the image available sizes
        $availableSizes = config($this->configKey . '.images.' . $imageKey . '.' . 'available_sizes');
        // we delete each sized image
        foreach (array_keys($availableSizes) as $sizeKey) {
            // we set the sized image name
            $sizeImageName = $imgNameWithoutExt . '-' . $sizeKey . '.' . $extension;
            // we get the sized image path
            $sizeImagePath = $this->getStoragePath($sizeImageName);
            // we check if the sized image file exists
            if (is_file($sizeImagePath)) {
                // we delete it
                File::delete($sizeImagePath);
                // we check that the image file has really been deleted
                if (is_file($sizeImagePath)) {
                    throw new ErrorException(get_class($this) . ' : the image removal went wrong. The image '
                                             . $sizeImagePath . ' still exists.');
                };
            }
        }
        // we delete the original image file
        $originalImagePath = $this->getStoragePath($imageName);
        if (is_file($originalImagePath)) {
            File::delete($originalImagePath);
            // we check that the image file has really been deleted
            if (is_file($originalImagePath)) {
                throw new ErrorException(get_class($this) . ' : the image removal went wrong. The image '
                                         . $originalImagePath . ' still exists.');
            };
        }
    }

    /**
     * @param string $imageKey
     * @param string $srcPath
     * @param string $extension
     * @param bool   $removeSrc
     *
     * @return string
     */
    protected function storeImageProcess(string $imageKey, string $srcPath, string $extension, bool $removeSrc = true)
    {
        // we make sure that the extension is in low case
        $extension = strtolower($extension);
        // we get and give a version number to the image name
        $imageName = str_slug($this->setImageVersion(
            config('files.prefix') . config($this->configKey . '.images.' . $imageKey . '.' . 'name')
        ));
        // we get the image available sizes
        $availableSizes = $this->getImageAvailableSizes($imageKey);
        // we set the stored image path
        $storedImagePath = $this->getStoragePath($imageName . '.' . $extension);
        // we copy the uploaded file to the storage path
        File::copy($srcPath, $storedImagePath);
        // we give the file correct permissions
        chmod($storedImagePath, 0644);
        // we resize and optimize the image
        $this->resizeImageProcess($imageName, $extension, $availableSizes);
        // we remove the source file
        if ($removeSrc) {
            File::delete($srcPath);
        }

        return $imageName . '.' . $extension;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    protected function setImageVersion(string $fileName)
    {
        // we set the new versioned image name
        $versionedImgName = $fileName . '-' . mt_rand(1000000000, 9999999999);

        return $versionedImgName;
    }

    /**
     * @param string $imageName
     * @param string $extension
     * @param array  $availableSizes
     */
    protected function resizeImageProcess(string $imageName, string $extension, array $availableSizes)
    {
        // we set the original image path
        $originalImg = Image::make($this->getStoragePath($imageName . '.' . $extension));
        // we resize the original image
        foreach ($availableSizes as $sizeKey => $sizeValues) {
            $originalImgInstance = clone $originalImg;
            // we get the size with and height
            $width = array_first($sizeValues);
            $height = array_last($sizeValues);
            switch (true) {
                // the width and the height are given
                case $width && $height:
                    $originalImgInstance->fit($width, $height, function($constraint) {
                        $constraint->upsize();
                    });
                    break;
                // only width or height is given
                case $width && !$height:
                case !$width && $height:
                    $originalImgInstance->resize($width, $height, function($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    break;
            }
            // we set the resized file name
            $resizedImgPath = $this->getStoragePath($imageName . '-' . $sizeKey . '.' . $extension);
            // we store the resized image
            $originalImgInstance->save($resizedImgPath);
            // we optimize the resized image
            if (config('repository.image_optimization')) {
                app(OptimizerChain::class)->optimizeImage($resizedImgPath);
            }
        }
    }

    /**
     * Store the given image from its path, according to the configuration available sizes
     *
     * @param string $imageKey
     * @param string $imgPath
     * @param bool   $removeSource
     *
     * @return string
     */
    public function storeImageFromPath(string $imageKey, string $imgPath, bool $removeSource = false)
    {
        // we check if the source image exists
        if (!is_file($imgPath)) {
            throw new InvalidArgumentException(
                get_class($this) . ' : the source image "' . $imgPath . '" does not exist.'
            );
        }
        // we get the image extension
        if (!$extension = pathinfo($imgPath, PATHINFO_EXTENSION)) {
            throw new InvalidArgumentException(
                get_class($this) . ' : the given image "' . $imgPath . '" has no extension.'
            );
        };
        // we remove the current image
        if ($currentImageName = $this->getCurrentImageName($imageKey)) {
            $this->destroyImage($imageKey, $currentImageName);
        }
        // we store the image
        $imageName = $this->storeImageProcess(
            $imageKey,
            $imgPath,
            $extension,
            $removeSource
        );

        return $imageName;
    }

    /**
     * Get the image path from its image config key and its size
     *
     * @param string      $imageName
     * @param string|null $sizeKey
     *
     * @return string
     */
    public function getImagePath(string $imageName, string $sizeKey = null)
    {
        // we no size key is given, we return the original image
        if (!$sizeKey) {
            return asset($this->getPublicPath($imageName));
        }
        // we get the image extension
        if (!$extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION))) {
            throw new InvalidArgumentException(get_class($this) . ' : the given image "' . $imageName
                                               . '" has no extension.');
        };
        // we get the image name without its extension
        $imgNameWithoutExt = str_slug(pathinfo($imageName, PATHINFO_FILENAME));
        $imagePath = $this->getPublicPath($imgNameWithoutExt . '-' . $sizeKey . '.' . $extension);

        return asset($imagePath);
    }

    /**
     * Get the authorized extensions formatted to be readable by humans
     *
     * @param string $imageKey
     * @param bool   $withoutSpaces
     *
     * @return string
     */
    public function getReadableImageAuthorizedExtensions(string $imageKey, bool $withoutSpaces = false)
    {
        return implode($withoutSpaces ? ',' : ', ', $this->getImageAuthorizedExtensions($imageKey));
    }

    /**
     * @param string $imageKey
     *
     * @return array
     */
    protected function getImageAuthorizedExtensions(string $imageKey)
    {
        // we get the image config
        $imageConfig = $this->getImageConfig($imageKey);

        return $imageConfig['authorized_extensions'];
    }
}