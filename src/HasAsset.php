<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia\HasMedia;

interface HasAsset extends HasMedia
{
    public function assetRelation(): MorphToMany;

    public function asset(string $type, ?string $locale = null): ?Asset;

    public function assets(string $type, string $locale = null): Collection;
}
