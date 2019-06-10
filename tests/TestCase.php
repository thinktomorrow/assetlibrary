<?php

namespace Thinktomorrow\AssetLibrary\Tests;

use Mockery;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Thinktomorrow\AssetLibrary\Tests\AssetlibraryDatabaseTransactions;

class TestCase extends BaseTestCase
{
    use TestHelpers, AssetlibraryDatabaseTransactions;

    protected $protectTestEnvironment = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->protectTestEnvironment();

        $this->setUpDatabase();

        config(['app.fallback_locale' => 'nl']);
    }

    public function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler{
            public function __construct(){}
            public function report(\Exception $e){}
            public function render($request, \Exception $e){ throw $e; }
        });
    }

    protected function protectTestEnvironment()
    {
        if( ! $this->protectTestEnvironment) return;

        if("testing" !== $this->app->environment())
        {
            throw new \Exception('Make sure your testing environment is properly set. You are now running tests in the ['.$this->app->environment().'] environment');
        }

        if(DB::getName() != "testing" && DB::getName() != "setup")
        {
            throw new \Exception('Make sure to use a dedicated testing database connection. Currently you are using ['.DB::getName().']. Are you crazy?');
        }
        // $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
        //     $table->increments('id');
        // });
        // Article::create();
        // include_once __DIR__.'/../database/migrations/2019_01_10_154909_create_media_table.php';
        // include_once __DIR__.'/../database/migrations/2019_01_10_154910_create_asset_table.php';
        // (new \CreateAssetTable())->up();
        // (new \CreateMediaTable())->up();
    }

     /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
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
        ]);
        $app['config']->set('filesystems.disks.secondMediaDisk', [
            'driver' => 'local',
            'root' => $this->getTempDirectory('media2'),
        ]);
        $app->bind('path.public', function () {
            return $this->getTempDirectory();
        });
        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
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
