<?php

namespace Thinktomorrow\AssetLibrary\Traits;

use Thinktomorrow\AssetLibrary\Models\Asset;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    /**
     * @return mixed
     */
    public function assetRelation(): MorphToMany
    {
        return $this->morphToMany(Asset::class, 'entity', 'asset_pivots')->withPivot('type', 'locale', 'order')->orderBy('order');
    }

    public function asset(string $type = '', ?string $locale = null): ?Asset
    {
        $this->load('assetRelation');

        if ($this->assetRelation->first() === null || $this->assetRelation->first()->pivot === null) {
            return null;
        }

        $assets = $this->assetRelation;

        if($type != '')
        {
            $assets = $this->assetRelation->where('pivot.type', $type);
        }

        if ($locale && $assets->count() > 1) {
            $assets = $assets->where('pivot.locale', $locale);
        }

        if ($assets->isEmpty()) {
            return null;
        }

        return $assets->first();
    }

    public function assets(string $type = '', string $locale = null)
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
