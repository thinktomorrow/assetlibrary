<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\HasAsset;

class SortAssets
{
    /**
     * Remove the asset and attaches a new one.
     *
     * @param $replace
     * @param $with
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function handle(HasAsset $model, $type, $sorting)
    {
        $files = $model->assets($type);
        $files->each(function ($asset) use ($model, $sorting) {
            if (in_array($asset->id, $sorting)) {
                $pivot = $model->assetRelation->find($asset->id)->pivot;
                $pivot->order = array_search($asset->id, $sorting);
                $pivot->save();
            }
        });
    }
}
