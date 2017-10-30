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
     * @param string $locale
     * @return bool
     */
    public function hasFile($type = '', $locale = ''): bool
    {
        $filename = $this->getFilename($type, $locale);

        return (bool) $filename and basename($filename) != 'other.png';
    }

    /**
     * @param string $type
     * @param string $locale
     * @return string
     */
    public function getFilename($type = '', $locale = '')
    {
        return basename($this->getFileUrl($type, '', $locale));
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getFileUrl($type = '', $size = '', $locale = null)
    {
        if ($this->assets->first() === null || $this->assets->first()->pivot === null) {
            return;
        }

        if (! $locale) {
            $locale = Locale::getDefault();
        }

        $assets = $this->assets->where('pivot.type', $type);
        if ($assets->count() > 1) {
            $assets = $assets->where('pivot.locale', $locale);
        }

        if ($assets->isEmpty()) {
            return;
        }

        return $assets->first()->getFileUrl($size);
    }

    /**
     * Adds a file to this model, accepts a type and locale to be saved with the file.
     *
     * @param $file
     * @param $type
     * @param string $locale
     */
    public function addFile($file, $type = '', $locale = null)
    {
        $locale = $this->normalizeLocale($locale);

        $asset = AssetUploader::upload($file);

        if ($asset instanceof Collection) {
            $asset->each->attachToModel($this, $type, $locale);
        } else {
            $asset->attachToModel($this, $type, $locale);
        }
    }

    /**
     * @return mixed
     */
    public function getAllImages()
    {
        $images = $this->assets->filter(function ($asset) {
            return $asset->getExtensionForFilter() == 'image';
        });

        return $images;
    }

    /**
     * @param null $type
     * @param string $locale
     * @return mixed
     */
    public function getAllFiles($type = null, $locale = '')
    {
        $locale = $this->normalizeLocale($locale);

        $files = $this->assets->where('pivot.type', $type)->where('pivot.locale', $locale);

        return $files;
    }

    /**
     * @param string|null $locale
     * @return null|string
     */
    private function normalizeLocale($locale): ?string
    {
        if ($locale === '' || $locale === null) {
            $locale = Locale::getDefault();
        }
        return $locale;
    }
}
