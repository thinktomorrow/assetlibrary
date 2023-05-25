<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\HasAsset;

class UpdateAssetData
{
    public function handle(HasAsset $model, Asset $asset, string $type, string $locale, array $data): void
    {
        $model->assetRelation()
            ->where('id', $asset->id)
            ->where('type', $type)
            ->where('locale', $locale)
            ->update(['data' => $data]);
    }
}
