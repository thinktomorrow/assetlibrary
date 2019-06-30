<?php

namespace Thinktomorrow\AssetLibrary\Traits;

use Thinktomorrow\AssetLibrary\Models\Asset;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Thinktomorrow\AssetLibrary\Models\AssetLibrary;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;

trait AssetTrait
{
    use HasMediaTrait;

    public static function bootAssetTrait()
    {
        static::deleted(function ($model) {
            $model->assets->each(function ($asset) use ($model) {
                $model->assets()->updateExistingPivot($asset->id, ['unused'=> true]);
            });
        });
    }

    /**
     * @return mixed
     */
    public function assets()
    {
        return $this->morphToMany(Asset::class, 'entity', 'asset_pivots')->withPivot('type', 'locale', 'order')->orderBy('order');
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

        $locale = $this->normalizeLocaleString($locale);

        $assets = $this->assets->where('pivot.type', $type);

        if ($locale && $assets->count() > 1) {
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
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function addFile($file, $type = '', $locale = null, $filename = null)
    {
        if (is_iterable($file)) {
            return $this->addFiles($file, $type, $locale);
        } else {
            $locale = $this->normalizeLocaleString($locale);

            if (is_string($file)) {
                $asset = AssetUploader::uploadFromBase64($file, $filename);
            } else {
                $asset = AssetUploader::upload($file, $filename);
            }

            if ($asset instanceof Asset) {
                $asset->attachToModel($this, $type, $locale);
            }

            return $asset;
        }
    }

    /**
     * Adds multiple files to this model, accepts a type and locale to be saved with the file.
     *
     * @param $files
     * @param string $type
     * @param string|null $locale
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function addFiles($files, $type = '', $locale = null)
    {
        $files  = (array) $files;
        $locale = $this->normalizeLocaleString($locale);
        $assets = collect();

        foreach ($files as $filename => $file) {
            $filename = is_string($filename) ? $filename : '';
            $assets->push($this->addFile($file, $type, $locale, $filename));
        }

        return $assets;
    }

    /**
     * @return mixed
     */
    public function getAllImages()
    {
        $images = $this->assets->filter(function ($asset) {
            return $asset->getExtensionForFilter() === 'image';
        });

        return $images->sortBy('pivot.order');
    }

    /**
     * Removes an asset completely.
     *
     * @param $ids
     */
    public function deleteAsset($ids): void
    {
        AssetLibrary::removeByIds($ids);
    }

    /**
     * Removes all assets completely.
     *
     * @param $ids
     */
    public function deleteAllAssets(): void
    {
        $this->assets->each->delete();
    }

    /**
     * Remove the asset and attaches a new one.
     *
     * @param $replace
     * @param $with
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function replaceAsset($replace, $with)
    {
        $old = $this->assets()->findOrFail($replace);

        $this->assets()->detach($old->id);
        $old->delete();

        $this->addFile(Asset::findOrFail($with), $old->pivot->type, $old->pivot->locale);
    }

    /**
     * @param null $type
     * @param string|null $locale
     * @return mixed
     */
    public function getAllFiles($type = null, $locale = null)
    {
        $assets = $this->assets;

        $locale = $this->normalizeLocaleString($locale);

        if ($type) {
            $assets = $assets->where('pivot.type', $type);
        }

        if ($locale) {
            $assets = $assets->where('pivot.locale', $locale);
        }

        return $assets->sortBy('pivot.order');
    }

    /**
     * @param null $type
     * @param $sorting
     */
    public function sortFiles($type, $sorting): void
    {
        $files = $this->getAllFiles($type);
        $files->each(function ($asset) use ($sorting) {
            if (in_array($asset->id, $sorting)) {
                $pivot = $this->assets->find($asset->id)->pivot;
                $pivot->order = array_search($asset->id, $sorting);
                $pivot->save();
            }
        });
    }

    /**
     * @param string|null $locale
     * @return string
     */
    private function normalizeLocaleString($locale = null): string
    {
        $locale = $locale ?? config('app.fallback_locale');

        return $locale;
    }
}
