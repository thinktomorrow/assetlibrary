<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\HasAsset;

class DetachAsset
{
    public function handle(HasAsset&Model $model, string $type, string $locale, array $assetIds): void
    {
        DB::table('assets_pivot')
            ->where('entity_type', $model->getMorphClass())
            ->where('entity_id', (string) $model->getKey())
            ->whereIn('asset_id', $assetIds)
            ->where('type', $type)
            ->where('locale', $locale)
            ->delete();
    }

    public function handleByType(HasAsset&Model $model, string $type): void
    {
        DB::table('assets_pivot')
            ->where('entity_type', $model->getMorphClass())
            ->where('entity_id', (string) $model->getKey())
            ->where('type', $type)
            ->delete();
    }

    public function handleAll(HasAsset&Model $model): void
    {
        DB::table('assets_pivot')
            ->where('entity_type', $model->getMorphClass())
            ->where('entity_id', (string) $model->getKey())
            ->delete();
    }
}
