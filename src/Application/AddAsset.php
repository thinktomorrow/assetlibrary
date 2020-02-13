<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException;
use Thinktomorrow\AssetLibrary\HasAsset;

class AddAsset
{
    private $order;

    /**
     * Add a file to this model, accepts a type and locale to be saved with the file.
     *
     * @param \Thinktomorrow\AssetLibrary\HasAsset $model
     * @param $file
     * @param string $type
     * @param string $locale
     * @return Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function add(HasAsset $model, $file, string $type, string $locale, ?string $filename = null): Asset
    {
        $asset = $this->uploadAssetFromInput($file, $filename);

        $this->attachAssetToModel($asset, $model, $type, $locale);

        return $asset;
    }

    /**
     * Adds multiple files to this model, accepts a type and locale to be saved with the file.
     *
     * @param $files
     * @param string $type
     * @param string $locale
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function addMultiple(HasAsset $model, Collection $files, string $type, string $locale): Collection
    {
        $assets = collect();

        $files->each(function ($file, $filename) use ($assets, $model, $type, $locale) {
            $filename = is_string($filename) ? $filename : '';
            $assets->push($this->add($model, $file, $type, $locale, $filename));
        });

        return $assets;
    }

    public function setOrder(int $order = null): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Attaches this asset instance to the given model and
     * sets the type and locale to the given values and
     * returns the model with the asset relationship.
     *
     * @param HasAsset $model
     * @param string $type
     * @param null|string $locale
     * @param null|int $order
     * @return void
     * @throws AssetUploadException
     */
    private function attachAssetToModel(Asset $asset, HasAsset $model, string $type, string $locale): void
    {
        $model->assetRelation()->attach($asset, ['type' => $type, 'locale' => $locale, 'order' => $this->order]);
    }

    private function uploadAssetFromInput($file, ?string $filename = null): Asset
    {
        if ($file instanceof Asset) {
            return $file;
        }

        if (is_string($file)) {
            return AssetUploader::uploadFromBase64($file, $filename);
        }

        return AssetUploader::upload($file, $filename);
    }
}
