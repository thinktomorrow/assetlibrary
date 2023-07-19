<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\AssetType;

interface HasAssetType
{
    /**
     * Unique identifier of the asset type this class belongs to.
     *
     * @return string
     */
    public function getAssetType(): string;

    /**
     * Eloquent scope method to specify a query to only match results
     * for a given morphable class
     *
     * @param $query
     * @param string|null $assetType
     * @return mixed
     */
    public function scopeAssetType($query, string $assetType = null);

    /**
     * Ignore the global morphable scope and fetch all results,
     * regardless of the current global collection scope.
     */
    public static function ignoreAssetType();
}
