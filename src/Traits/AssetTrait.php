<?php

namespace Thinktomorrow\AssetLibrary\Traits;

use Illuminate\Support\Collection;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;
use Thinktomorrow\Locale\Locale;

trait AssetTrait
{
    /**
     * @return mixed
     */
    public function assets()
    {
        return $this->morphToMany(Asset::class, 'entity', 'asset_pivots')->withPivot('type', 'locale');
    }

    /**
     * @param string $type
     * @param string|null $locale
     * @return bool
     */
    public function hasFile($type = '', $locale = null): bool
    {
        $filename = $this->getFilename($type, $locale);

        return (bool) $filename && basename($filename) != 'other.png';
    }

    /**
     * @param string $type
     * @param string|null $locale
     * @return string
     */
    public function getFilename($type = '', $locale = null): string
    {
        return basename($this->getFileUrl($type, '', $locale));
    }

    /**
     * @param string $type
     * @param string $size
     * @param string|null $locale
     * @return string
     */
    public function getFileUrl($type = '', $size = '', $locale = null): ?string
    {
        if ($this->assets->first() === null || $this->assets->first()->pivot === null) {
            return null;
        }

        $locale = $this->normalizeLocale($locale);

        $assets = $this->assets->where('pivot.type', $type);
        if ($assets->count() > 1) {
            $assets = $assets->where('pivot.locale', $locale);
        }

        if ($assets->isEmpty()) {
            return null;
        }

        return $assets->first()->getFileUrl($size);
    }

    /**
     * Adds a file to this model, accepts a type and locale to be saved with the file.
     *
     * @param $file
     * @param string $type
     * @param string|null $locale
     * @param null $filename
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function addFile($file, $type = '', $locale = null, $filename = null): void
    {
        $locale = $this->normalizeLocale($locale);

        if(is_string($file))
        {
            $asset = AssetUploader::uploadFromBase64($file, $filename);
        }else{
            $asset = AssetUploader::upload($file, $filename);
        }
        if($asset instanceof Asset){
            $asset->attachToModel($this, $type, $locale);
        }
    }

    /**
     * Adds multiple files to this model, accepts a type and locale to be saved with the file.
     *
     * @param $files
     * @param $type
     * @param string|null $locale
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function addFiles($files, $type = '', $locale = null): void
    {
        $files = (array) $files;
        $locale = $this->normalizeLocale($locale);

        $asset = AssetUploader::upload($files);

        collect($asset)->each->attachToModel($this, $type, $locale);
    }

    /**
     * @return mixed
     */
    public function getAllImages()
    {
        $images = $this->assets->filter(function ($asset) {
            return $asset->getExtensionForFilter() === 'image';
        });

        return $images;
    }

    /**
     * Removes an asset completely.
     *
     * @param $ids
     */
    public function deleteAsset($ids): void
    {
        Asset::remove($ids);
    }

    /**
     * Remove the asset and attaches a new one.
     *
     * @param $replace
     * @param $with
     */
    public function replace($replace, $with): void
    {
        $asset = Asset::findOrFail($replace);
        Asset::remove($replace);

        $this->addFile(Asset::findOrFail($with), $asset->type, $asset->locale);
    }

    /**
     * @param null $type
     * @param string|null $locale
     * @return mixed
     */
    public function getAllFiles($type = null, $locale = null)
    {
        $locale = $this->normalizeLocale($locale);

        $files = $this->assets->where('pivot.type', $type)->where('pivot.locale', $locale);

        return $files;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    private function normalizeLocale($locale = null): string
    {
        $locale = $locale ?? Locale::getDefault();

        return $locale;
    }
}
