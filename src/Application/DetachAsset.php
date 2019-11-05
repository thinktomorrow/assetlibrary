<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\HasAsset;

class DetachAsset
{
    /**
     * Detaches an asset from a model.
     *
     * @param $ids
     */
    public function detach(HasAsset $model, $ids): void
    {
        if (! is_array($ids)) $ids = (array) $ids;
        
        foreach ($ids as $id) {
            $model->assetRelation()->detach($id);
        }
    }

    /**
     * Detaches all assets or for a specific type from a model.
     *
     * @param $ids
     */
    public function detachAll(HasAsset $model, ?string $type = null): void
    {
        $builder = $model->assetRelation();

        if($type) {
            $ids = $builder->where('asset_pivots.type', $type)->get()->pluck('id');
            $builder->detach($ids);
        }else{
            $builder->detach();
        }
        
    }
}
