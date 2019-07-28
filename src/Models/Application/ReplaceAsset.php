<?php

namespace Thinktomorrow\AssetLibrary\Models\Application;

use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Interfaces\HasAsset;
use Thinktomorrow\AssetLibrary\Models\Application\AddAsset;

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

        $model->assetRelation()->detach($old->id);
        $old->delete();

        app(AddAsset::class)->add($model, Asset::findOrFail($with), $old->pivot->type, $old->pivot->locale);
    }
}
