<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

        DB::table('asset_pivots')
            ->where('entity_type', $model->getMorphClass())
            ->where('entity_id', (string) $model->getKey())
            ->whereIn('asset_id', $ids)
            ->where('type', $type)
            ->where('locale', $locale)
            ->delete();
    }

    /**
     * Detaches all assets or for a specific type from a model.
     *
     * @param $ids
     */
    public function detachAll(HasAsset&Model $model, ?string $type = null): void
    {
        $query = DB::table('asset_pivots')
            ->where('entity_type', $model->getMorphClass())
            ->where('entity_id', (string) $model->getKey());

        if($type) {
            $query->where('type', $type);
        }

        $query->delete();
    }

    /**
     * @param mixed $ids
     * @return string[]
     */
    private function ensureIdsArePassedAsString(array $ids): array
    {
        return array_map(fn($id) => (string)$id, $ids);
    }
}
