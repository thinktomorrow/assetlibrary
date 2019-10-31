<?php

namespace Thinktomorrow\AssetLibrary\Tests;

use Illuminate\Foundation\Exceptions\Handler;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Contracts\Debug\ExceptionHandler;
<<<<<<< HEAD
=======
use Thinktomorrow\AssetLibrary\Test\stubs\Article;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Svg;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Webp;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Image;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Video;
>>>>>>> master

abstract class TestCase extends Orchestra
{
    protected $protectTestEnvironment = true;
    protected static $migrationsRun   = false;

    /** @var \Thinktomorrow\AssetLibrary\Tests\stubs\Article */
    protected $testArticle;

<<<<<<< HEAD
    use TestHelpers, DatabaseTransactions;
=======
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->testArticle = Article::first();
    }
>>>>>>> master

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct()
            {
            }

            public function report(\Exception $e)
            {
            }

            public function render($request, \Exception $e)
            {
                throw $e;
            }
        });
    }

    /**
     * @param \Illuminate\Foundation\Application $app
<<<<<<< HEAD
=======
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
        });
        Article::create();
        include_once __DIR__.'/../database/migrations/2019_01_10_154909_create_media_table.php';
        include_once __DIR__.'/../database/migrations/2019_01_10_154910_create_asset_table.php';
        (new \CreateAssetTable())->up();
        (new \CreateMediaTable())->up();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
>>>>>>> master
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            \Thinktomorrow\AssetLibrary\AssetLibraryServiceProvider::class,
            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
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
        ]);
        $app['config']->set('filesystems.disks.secondMediaDisk', [
            'driver' => 'local',
            'root' => $this->getTempDirectory('media2'),
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
