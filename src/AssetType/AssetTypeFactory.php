<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\AssetType;

use Thinktomorrow\AssetLibrary\AssetContract;

class AssetTypeFactory
{
    /**
     * Get the className for the given asset type. If asset type is
     * not found, an exception is thrown in your face.
     *
     * @param string $assetType
     * @return string
     */
    public static function className(string $assetType): string
    {
        if($className = config('thinktomorrow.assetlibrary.types.'.$assetType)) {
            return $className;
        }

        throw new NotFoundAssetType('No class found by assetType [' . $assetType . ']. Make sure that the assetType is a valid class reference.');
    }

    public static function assetTypeByClassName(string $className): string
    {
        foreach(config('thinktomorrow.assetlibrary.types') as $type => $class) {
            if($className == $class) {
                return $type;
            }
        }

        throw new NotFoundAssetType('No asset type found by className [' . $className . ']. Make sure that the entry exists in config.');
    }

    public static function instance(string $assetType, $attributes = []): AssetContract
    {
        if(! isset($attributes['asset_type'])) {
            $attributes['asset_type'] = $assetType;
        }

        $className = static::className($assetType);

        return new $className($attributes);
    }
}
