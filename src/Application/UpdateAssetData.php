<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\HasAsset;

class UpdateAssetData
{
    public function handle(HasAsset $model, string $assetId, string $type, string $locale, array $data): void
    {
        DB::table('assets_pivot')
            ->where('entity_type', $model->getMorphClass())
            ->where('entity_id', (string) $model->getKey())
            ->where('asset_id', $assetId)
            ->where('type', $type)
            ->where('locale', $locale)
            ->update(['data' => $data]);
    }
}
