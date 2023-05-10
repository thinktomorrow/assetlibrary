<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\HasAsset;

class AddAsset
{
    public function handle(HasAsset $model, Asset $asset, string $type, string $locale, int $order, array $data): void
    {
        $model->assetRelation()->attach($asset, [
            'type' => $type,
            'locale' => $locale,
            'order' => $order,
            'data' => $data,
        ]);
    }
}
