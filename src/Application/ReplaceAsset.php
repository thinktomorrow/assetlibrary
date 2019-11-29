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
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function handle(HasAsset $model, $replace, $with)
    {
        $old = $model->assetRelation()->findOrFail($replace);

        app(AddAsset::class)->add($model, Asset::findOrFail($with), $old->pivot->type, $old->pivot->locale);

        app(DetachAsset::class)->detach($model, $old->id, $old->pivot->type, $old->pivot->locale);
    }
}
