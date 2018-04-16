<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\ServiceProvider;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class AssetLibraryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/../config/assetlibrary.php' => config_path('assetlibrary.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/assetlibrary.php', 'assetlibrary');

        $this->app->singleton('asset', function ($app) {
            return new Asset($app);
        });

        $this->publishMigrations();

    }

    public function publishMigrations(): void
    {
        if (! class_exists('CreateAssetTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_asset_table.php' => database_path('migrations/'.date('Y_m_d_His',
                        time()).'_create_asset_table.php'),
            ], 'migrations');
        }

        if (! class_exists('CreateMediaTable')) {
            $this->publishes([
                __DIR__.'/../../../spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_media_table.php'),
            ], 'migrations');
        }
    }

    private function getConfig()
    {
        if (file_exists(config_path('thinktomorrow/assetlibrary.php'))) {
            return require config_path('thinktomorrow/assetlibrary.php');
        }
        return require __DIR__.'/../config/assetlibrary.php';
    }
}
