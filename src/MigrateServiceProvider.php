<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\ServiceProvider;
use Thinktomorrow\AssetLibrary\Commands\ImageToAssetMigrateCommand;

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
