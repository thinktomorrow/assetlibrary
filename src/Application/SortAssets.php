<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\HasAsset;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class SortAssets
{
    /**
     * Remove the asset and attaches a new one.
     *
     * @param $replace
     * @param $with
     * @throws FileCannotBeAdded
     */
    public function handle(HasAsset $model, $sorting, $type, $locale)
    {
        $assets = $model->assetRelation()->where('asset_pivots.type', $type)->where('asset_pivots.locale', $locale)->get();

        foreach ($assets as $asset) {
            $pivot        = $asset->pivot;
            $pivot->order = array_search($asset->id, $sorting);

            $pivot->save();
        }
    }
}
