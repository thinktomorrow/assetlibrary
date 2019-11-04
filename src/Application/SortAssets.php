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
        $assets = $model->assetRelation()->where('asset_pivots.type', $type)->get();

        foreach ($assets as $asset) {
            $pivot = $asset->pivot;
            $pivot->order = array_search($asset->id, $sorting);

            $pivot->save();
        }
    }
}
