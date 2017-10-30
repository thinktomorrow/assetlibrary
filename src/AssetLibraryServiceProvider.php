<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Thinktomorrow\AssetLibrary\Models\Asset;

class AssetLibraryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/thinktomorrow/assetlibrary.php' => config_path('assetlibrary.php'),
        ], 'config');

        $this->setupRoutes($this->app->router);

        $this->publishMigrations();

        $this->registerModelBindings();
        $this->registerEloquentFactoriesFrom(__DIR__.'/../database/factories');
    }

    /**
     * Register factories.
     *
     * @param  string  $path
     * @return void
     */
    protected function registerEloquentFactoriesFrom($path)
    {
        $this->app->make(EloquentFactory::class)->load($path);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $router->group(['namespace' => 'Thinktomorrow\AssetLibrary\Http\Controllers'], function ($router) {
            require __DIR__.'/Http/routes.php';
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/assetlibrary.php', 'assetlibrary');

        $this->registerAssetLibrary();
    }

    protected function registerModelBindings()
    {
        //TODO implement this
    }

    /**
     *
     */
    private function registerAssetLibrary()
    {
        $this->app->singleton('asset', function ($app) {
            return new Asset($app);
        });
    }

    /**
     *
     */
    public function publishMigrations(): void
    {
        if (!class_exists('CreateAssetTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_asset_table.php' => database_path('migrations/' . date('Y_m_d_His',
                        time()) . '_create_asset_table.php'),
            ], 'migrations');
        }
    }
}
