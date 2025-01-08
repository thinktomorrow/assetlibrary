<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * @method getMorphClass()
 * @method getKey()
 */
interface HasAsset
{
    public function assetRelation(): MorphToMany;

    public function asset(string $type, ?string $locale = null): ?AssetContract;

    public function assets(string $type, ?string $locale = null): Collection;
}
