<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AssetLibraryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/assetlibrary.php' => config_path('thinktomorrow/assetlibrary.php'),
        ], 'assetlibrary-config');

        if (! config('thinktomorrow.assetlibrary.types.default')) {
            config()->set('thinktomorrow.assetlibrary.types.default', Asset::class);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Relation::morphMap(['asset' => Asset::class]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(MediaLibraryServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../config/assetlibrary.php', 'thinktomorrow.assetlibrary');
    }
}
