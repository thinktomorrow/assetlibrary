<?php

namespace Thinktomorrow\AssetLibrary\Models\Application;

use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Interfaces\HasAsset;
use Thinktomorrow\AssetLibrary\Models\Application\AddAsset;

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
