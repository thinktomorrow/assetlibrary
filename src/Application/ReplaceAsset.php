<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\HasAsset;

class ReplaceAsset
{
    /**
     * Remove the asset and attaches a new one.
     *
     * @param $replace
     * @param $with
     * @param $type
     * @param $locale
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function handle(HasAsset $model, $replace, $with, $type, $locale)
    {
        $old = $model->assetRelation()->findOrFail($replace);

        app(AddAsset::class)->add($model, Asset::findOrFail($with), $type, $locale);

        app(DetachAsset::class)->detach($model, $old->id, $type, $locale);
    }
}
