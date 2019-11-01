<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

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
        return $this->assets($type, $locale)->first();
    }

    public function assets(?string $type = null, ?string $locale = null): Collection
    {
        $assets = $this->assetRelation;

        if ($type) {
            $assets = $assets->where('pivot.type', $type);
        }

        $locale = $locale ?? app()->getLocale();

        $results = $assets->filter(function($asset) use($locale){
            return $asset->pivot->locale == $locale;
        });

        if($this->getUseAssetFallbackLocale() && $locale != $this->getAssetFallbackLocale() && $results->isEmpty()) {
            $results = $assets->filter(function($asset){
                return $asset->pivot->locale == $this->getAssetFallbackLocale();
            });
        }
        return $results->sortBy('pivot.order');
    }

    protected function getUseAssetFallbackLocale(): bool
    {
        return $this->useAssetFallbackLocale ?? config('thinktomorrow.assetlibrary.use_fallback_locale', false);
    }

    protected function getAssetFallbackLocale(): string
    {
        return $this->assetFallbackLocale ?? config('thinktomorrow.assetlibrary.fallback_locale');
    }
}
