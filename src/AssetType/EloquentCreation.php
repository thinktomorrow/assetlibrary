<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\AssetType;

trait EloquentCreation
{
    public static function create(array $attributes = [])
    {
        if (! isset($attributes['asset_type'])) {
            $attributes['asset_type'] = AssetTypeFactory::assetTypeByClassName(static::class);
        }

        $className = AssetTypeFactory::className($attributes['asset_type']);

        return $className::query()->create($attributes);
    }
}
