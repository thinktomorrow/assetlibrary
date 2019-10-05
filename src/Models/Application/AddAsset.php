<?php

namespace Thinktomorrow\AssetLibrary\Models\Application;

use Illuminate\Support\Collection;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Interfaces\HasAsset;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;

class AddAsset
{
    /**
     * Add a file to this model, accepts a type and locale to be saved with the file.
     *
     * @param \Thinktomorrow\AssetLibrary\Interfaces\HasAsset $model
     * @param $file
     * @param string $type
     * @param string|null $locale
     * @return Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function add(HasAsset $model, $file, string $type = '', ?string $locale = null, ?string $filename = null): Asset
    {
        $locale = $this->normalizeLocaleString($locale);

        if (is_string($file)) {
            $asset = AssetUploader::uploadFromBase64($file, $filename);
        } else {
            $asset = AssetUploader::upload($file, $filename);
        }

        if ($asset instanceof Asset) {
            $asset->attachToModel($model, $type, $locale);
        }

        return $asset;
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
    public function addMultiple(HasAsset $model, Collection $files, $type = '', $locale = null): Collection
    {
        $locale = $this->normalizeLocaleString($locale);
        $assets = collect();

        $files->each(function ($file, $filename) use ($assets, $model, $type, $locale) {
            $filename = is_string($filename) ? $filename : '';
            $assets->push($this->add($model, $file, $type, $locale, $filename));
        });

        return $assets;
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
