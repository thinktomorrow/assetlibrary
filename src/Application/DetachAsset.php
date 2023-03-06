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
    public function detach(HasAsset $model, $ids, $type, $locale): void
    {
        if (! is_array($ids)) {
            $ids = (array) $ids;
        }

        $ids = $this->ensureIdsArePassedAsString($ids);

        foreach ($ids as $id) {
            $model->assetRelation()->where('asset_pivots.type', $type)->where('asset_pivots.locale', $locale)->detach($id);
        }
    }

    /**
     * Detaches all assets or for a specific type from a model.
     *
     * @param $ids
     */
    public function detachAll(HasAsset $model, ?string $type = null): void
    {
        $assetIds = $type
            ? $model->assetRelation()->where('asset_pivots.type', $type)->get()->pluck('id')
            : $model->assetRelation()->get()->pluck('id');

        $assetIds = $this->ensureIdsArePassedAsString($assetIds);

        $model->assetRelation()->detach($assetIds);
    }

    /**
     * @param mixed $ids
     * @return string[]
     */
    public function ensureIdsArePassedAsString(mixed $ids): array
    {
        return array_map(fn($id) => (string)$id, $ids);
    }
}
