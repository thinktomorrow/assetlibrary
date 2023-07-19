<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\HasAsset;

class UpdateAssociatedAssetData
{
    public function handle(HasAsset $model, string $assetId, string $type, string $locale, array $data): void
    {
        $existingData = json_decode(
            DB::table('assets_pivot')
                ->where('entity_type', $model->getMorphClass())
                ->where('entity_id', (string) $model->getKey())
                ->where('asset_id', $assetId)
                ->where('type', $type)
                ->where('locale', $locale)
                ->select('data')
                ->first()->data, true
            );

        if(is_null($existingData)) {
            $existingData = [];
        }

        DB::table('assets_pivot')
            ->where('entity_type', $model->getMorphClass())
            ->where('entity_id', (string) $model->getKey())
            ->where('asset_id', $assetId)
            ->where('type', $type)
            ->where('locale', $locale)
            ->update(['data' => array_merge($existingData, $data)]);
    }
}
