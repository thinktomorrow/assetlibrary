<?php

namespace Thinktomorrow\AssetLibrary\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Image;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Svg;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Video;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Webp;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends BaseTestCase
{
    use TestHelpers, RefreshDatabase;

    protected $protectTestEnvironment = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->protectTestEnvironment();

        config(['app.fallback_locale' => 'nl']);

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'thumb' => [
                'width' => 50,
                'height' => 50,
            ],
            'full' => [
                'width' => 80,
                'height' => 100,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.formats', [

        ]);

        // Allow all types of files to be converted by default
        config()->set('thinktomorrow.assetlibrary.disable_conversions_for_mimetypes', [

        ]);
    }

    protected function protectTestEnvironment()
    {
        if (! $this->protectTestEnvironment) {
            return;
        }

        if ('testing' !== $this->app->environment()) {
            throw new \Exception('Make sure your testing environment is properly set. You are now running tests in the ['.$this->app->environment().'] environment');
        }

        if (DB::getName() != 'testing' && DB::getName() != 'setup') {
            throw new \Exception('Make sure to use a dedicated testing database connection. Currently you are using ['.DB::getName().']. Are you crazy?');
        }
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            MediaLibraryServiceProvider::class,
            \Thinktomorrow\AssetLibrary\AssetLibraryServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Connection is defined in the phpunit config xml
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', __DIR__.'/../database/testing.sqlite'),
            'prefix' => '',
        ]);

        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => $this->getMediaDirectory(),
            'url' => env('APP_URL') . '/media',
            'visibility' => 'public',
        ]);

        $app['config']->set('filesystems.disks.secondMediaDisk', [
            'driver' => 'local',
            'root' => $this->getTempDirectory('media2'),
            'url' => env('APP_URL') . '/media2',
        ]);

        $app->bind('path.public', function () {
            return $this->getTempDirectory();
        });
        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');

        $app['config']->set('medialibrary.image_generators', [
            Image::class,
            Webp::class,
            Svg::class,
            Video::class,
        ]);
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__.'/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    public function getMediaDirectory($suffix = '')
    {
        return $this->getTempDirectory().'/media'.($suffix == '' ? '' : '/'.$suffix);
    }
}
