<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\HasAsset;
use Thinktomorrow\AssetLibrary\Models\AssetLibrary;

class DeleteAsset
{
    /**
     * Removes an asset completely.
     *
     * @param $ids
     */
    public function delete($ids): void
    {
        AssetLibrary::removeByIds($ids);
    }

    /**
     * Removes all assets completely.
     *
     * @param $ids
     */
    public function deleteAll(HasAsset $model): void
    {
        $model->assetRelation->each->delete();
    }
}
