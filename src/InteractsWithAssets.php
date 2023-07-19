<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait InteractsWithAssets
{
    public static function bootInteractsWithAssets()
    {
        static::deleting(function ($model) {
            if(! isset($model->forceDeleting) || $model->forceDeleting === true) {
                $model->assetRelation()->detach();
            }
        });
    }

    public function assetRelation(): MorphToMany
    {
        // LocalAsset will be morphed into the specific asset models after the query
        return $this->morphToMany(Asset::class, 'entity', 'assets_pivot', 'entity_id', 'asset_id')
            ->withPivot('type', 'locale', 'order', 'data')
            ->orderBy('order')
            ->using(AssociatedAsset::class);
    }

    public function asset(?string $type = null, ?string $locale = 'DEFAULT_LOCALE'): ?AssetContract
    {
        return $this->assets($type, $locale)->first();
    }

    public function assets(?string $type = null, ?string $locale = 'DEFAULT_LOCALE'): Collection
    {
        $assets = $this->fetchAssets($type, $locale == 'DEFAULT_LOCALE' ? app()->getLocale() : $locale);

        if($assets->isEmpty() && $this->useAssetFallbackLocale() && $locale != $this->getAssetFallbackLocale()) {
            $assets = $this->fetchAssets($type, $this->getAssetFallbackLocale());
        }

        return $assets;
    }

    protected function useAssetFallbackLocale(): bool
    {
        return false !== config('thinktomorrow.assetlibrary.fallback_locale');
    }

    protected function getAssetFallbackLocale(): ?string
    {
        if(! $this->useAssetFallbackLocale()) {
            return null;
        }

        if(is_null($fallbackLocale = config('thinktomorrow.assetlibrary.fallback_locale'))) {
            $fallbackLocale = config('app.fallback_locale');
        }

        return $fallbackLocale;
    }

    private function fetchAssets(?string $type = null, ?string $locale = null): Collection
    {
        return $this->assetRelation
            ->when($type, fn ($collection) => $collection->where('pivot.type', $type))
            ->when($locale, fn ($collection) => $collection->filter(fn (AssetContract $asset) => $asset->pivot->locale == $locale))
            ->sortBy('pivot.order');
    }
}
