<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait InteractsWithAssets
{
    protected array $assetFallbackLocales = [];

    public static function bootInteractsWithAssets()
    {
        static::deleting(function ($model) {
            if (! isset($model->forceDeleting) || $model->forceDeleting === true) {
                DB::table('assets_pivot')
                    ->where('entity_id', (string) $model->id)
                    ->where('entity_type', $model->getMorphClass()) // Ensure this is a string
                    ->delete();
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
        $locale = $locale === 'DEFAULT_LOCALE' ? app()->getLocale() : $locale;

        $assets = $this->fetchAssets($type, $locale);

        /**
         * If the assets are empty and the use want to return assets for a given
         * locale, we will try to fetch the assets for the fallback locale.
         * If locale is passed as null, the user wants to explicitly
         * fetch assets without locale restrictions.
         */
        if ($locale) {
            $fallbackLocale = $this->getAssetFallbackLocaleFor($locale);

            while ($assets->isEmpty() && $fallbackLocale) {
                $assets = $this->fetchAssets($type, $fallbackLocale);

                $newFallbackLocale = $this->getAssetFallbackLocaleFor($fallbackLocale);
                $fallbackLocale = $newFallbackLocale === $fallbackLocale ? null : $newFallbackLocale;
            }
        }


        return $assets;
    }

    protected function useAssetFallbackLocale(): bool
    {
        return false !== config('thinktomorrow.assetlibrary.fallback_locale');
    }

    private function getAssetFallbackLocaleFor(string $locale): ?string
    {
        if (! $this->useAssetFallbackLocale()) {
            return null;
        }

        $fallbackLocales = $this->getAssetFallbackLocales();

        if (count($fallbackLocales) === 0 || ! isset($fallbackLocales[$locale])) {
            return $this->getDefaultAssetFallbackLocale();
        }

        return $fallbackLocales[$locale];
    }

    /**
     * A map of locales to fallback locales. e.g. ['en' => 'nl']
     */
    protected function getAssetFallbackLocales(): array
    {
        return $this->assetFallbackLocales;
    }

    public function setAssetFallbackLocales(array $fallbackLocales): void
    {
        $this->assetFallbackLocales = $fallbackLocales;
    }

    private function getDefaultAssetFallbackLocale(): ?string
    {
        if (is_null($fallbackLocale = config('thinktomorrow.assetlibrary.fallback_locale'))) {
            $fallbackLocale = config('app.fallback_locale');
        }

        return $fallbackLocale;
    }

    private function fetchAssets(?string $type = null, ?string $locale = null): Collection
    {
        return $this->assetRelation
            ->when($type, fn ($collection) => $collection->where('pivot.type', $type))
            ->when($locale, fn ($collection) => $collection->filter(fn (AssetContract $asset) => $asset->pivot->locale == $locale))
            ->sortBy('pivot.order')
            ->values();
    }
}
