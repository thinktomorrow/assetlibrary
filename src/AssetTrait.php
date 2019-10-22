<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Thinktomorrow\AssetLibrary\Models\Asset;

trait AssetTrait
{
    use HasMediaTrait;

    public static function bootAssetTrait()
    {
        static::deleted(function ($model) {
            $model->assetRelation->each(function ($asset) use ($model) {
                $model->assetRelation()->updateExistingPivot($asset->id, ['unused'=> true]);
            });
        });
    }

    public function assetRelation(): MorphToMany
    {
        return $this->morphToMany(Asset::class, 'entity', 'asset_pivots')->withPivot('type', 'locale', 'order')->orderBy('order');
    }

    public function asset(string $type, ?string $locale = null): ?Asset
    {
        $this->load('assetRelation');

        if ($this->assetRelation->first() === null || $this->assetRelation->first()->pivot === null) {
            return null;
        }

        $assets = $this->assetRelation->where('pivot.type', $type);

        if ($locale && $assets->count() > 1) {
            $assets = $assets->where('pivot.locale', $locale);
        }

        return $assets->first();
    }

    public function assets(string $type = '', ?string $locale = null): Collection
    {
        $this->load('assetRelation');

        $assets = $this->assetRelation;

        if ($type) {
            $assets = $assets->where('pivot.type', $type);
        }

        if ($locale) {
            $assets = $assets->where('pivot.locale', $locale);
        }

        return $assets->sortBy('pivot.order');
    }
}
