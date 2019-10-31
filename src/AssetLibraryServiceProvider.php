<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\ServiceProvider;
<<<<<<< HEAD
use Thinktomorrow\AssetLibrary\Commands\ImageToAssetMigrateCommand;
=======
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
>>>>>>> master

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
    public function boot()
    {
<<<<<<< HEAD
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/assetlibrary.php' => config_path('assetlibrary.php'),
            ], 'config');

            $this->mergeConfigFrom(__DIR__.'/../config/assetlibrary.php', 'assetlibrary');

            $this->publishMigrations();

            $this->app->bind('command.assetlibrary:migrate-image', ImageToAssetMigrateCommand::class);

            $this->commands([
                'command.assetlibrary:migrate-image',
            ]);
        }
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
=======
        (new MediaLibraryServiceProvider($this->app))->register();

        $this->mergeConfigFrom(__DIR__.'/../config/assetlibrary.php', 'assetlibrary');
>>>>>>> master
    }
}
