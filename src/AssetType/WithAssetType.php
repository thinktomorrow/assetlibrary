<?php

declare(strict_types=1);

namespace Thinktomorrow\AssetLibrary\AssetType;

trait WithAssetType
{
    use EloquentInstantiation;
    use EloquentCreation;

    public function getAssetType(): string
    {
        return $this->asset_type;
    }

    /**
     * Retrieve results for one specific type model.
     *
     * @param $query
     * @param string|null $assetType
     * @return mixed
     */
    public function scopeAssetType($query, ?string $assetType = null)
    {
        return $query->withoutGlobalScope(new GlobalAssetTypeScope())
            ->where('asset_type', '=', $assetType);
    }

    /**
     * Ignore the morphable scoping. This will fetch all results,
     * regardless of the specific morphable models.
     */
    public static function ignoreAssetType()
    {
        return self::withoutGlobalScope(new GlobalAssetTypeScope());
    }
}
