<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class AssetLibraryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        (new MediaLibraryServiceProvider($this->app))->boot();

        $this->publishes([
            __DIR__.'/../config/assetlibary.php' => config_path('thinktomorrow/assetlibrary.php'),
        ], 'assetlibrary-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function register()
    {
        (new MediaLibraryServiceProvider($this->app))->register();

        $this->mergeConfigFrom(__DIR__.'/../config/assetlibrary.php', 'thinktomorrow.assetlibrary');
    }
}
