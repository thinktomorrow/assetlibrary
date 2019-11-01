<?php

namespace Thinktomorrow\AssetLibraryMigrate;

use Illuminate\Support\ServiceProvider;

class MigrateServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function register()
    {
        if($this->app->runningInConsole()) {
            $this->app->bind('command.assetlibrary:migrate-image', ImageToAssetMigrateCommand::class);
            $this->commands([
                'command.assetlibrary:migrate-image',
            ]);
        }
    }
}
