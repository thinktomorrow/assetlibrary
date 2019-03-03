<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;

class ImageToAssetMigrateCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->testArticle = Article::create();
    }

    public function tearDown(): void
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /** @test */
    public function it_can_migrate_images_to_assets()
    {
        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);

        // assert the image exists on both locations
        $this->assertFileExists(public_path(Article::first()->imageurl));
        $this->assertFileExists(public_path(Article::first()->getFileUrl()));
        $this->assertequals($this->testArticle->getFileName(), 'warpaint-logo.svg');
    }

    /** @test */
    public function it_can_migrate_multiple_images()
    {
        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->save();

        $article           = Article::create();
        $article->imageurl = '/uploads/warpaint-logo.svg';
        $article->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);

        // assert the image exists on both locations
        $this->assertFileExists(public_path($this->testArticle->fresh()->imageurl));
        $this->assertFileExists(public_path($article->fresh()->imageurl));
        $this->assertFileExists(public_path($this->testArticle->getFileUrl()));
        $this->assertFileExists(public_path($article->getFileUrl()));
        $this->assertequals($article->fresh()->getFileName(), 'warpaint-logo.svg');
        $this->assertequals($this->testArticle->fresh()->getFileName(), 'warpaint-logo.svg');
    }

    /** @test */
    public function it_continues_if_an_image_doesnt_exist()
    {
        // fill db with an entry
        $this->testArticle->imageurl = 'foobar.svg';
        $this->testArticle->save();

        $article           = Article::create();
        $article->imageurl = '/uploads/warpaint-logo.svg';
        $article->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);
        // assert the image exists on both locations
        $this->assertFileExists(public_path($article->fresh()->imageurl));
        $this->assertFileExists(public_path($article->getFileUrl()));
        $this->assertequals($article->getFileName(), 'warpaint-logo.svg');
    }

    /** @test */
    public function it_can_remove_the_original_image()
    {
        // copy dummy image to remove it
        copy(public_path('/uploads/warpaint-logo.svg'), public_path('/uploads/warpaint-logo-duplicate.svg'));

        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo-duplicate.svg';
        $this->testArticle->save();

        // assert the duplicate file actually exists
        $this->assertFileExists(public_path($this->testArticle->fresh()->imageurl));

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                '--force'     => true,
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);
        // assert the image exists only on new location
        $this->assertFileNotExists(public_path($this->testArticle->fresh()->imageurl));
        $this->assertFileExists(public_path($this->testArticle->getFileUrl()));
        $this->assertequals($this->testArticle->getFileName(), 'warpaint-logo-duplicate.svg');
    }

    /** @test */
    public function it_can_remove_existing_assets_on_model()
    {
        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->save();

        $this->testArticle->addFile(UploadedFile::fake()->image('image.png'));

        $this->assertCount(1, $this->testArticle->assets);

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                '--reset'     => true,
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);
        // assert the orginal asset was removed
        $this->assertCount(1, $this->testArticle->assets);

        // assert the image exists on both locations
        $this->assertFileExists(public_path($this->testArticle->fresh()->imageurl));
        $this->assertequals($this->testArticle->fresh()->getFileName(), 'warpaint-logo.svg');
    }

    /** @test */
    public function it_can_migrate_images_with_order()
    {
        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->order    = 7;
        $this->testArticle->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'ordercolumn' => 'order',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);


        // assert order is set on the asset
        $this->assertEquals(7, $this->testArticle->fresh()->getAllImages()->first()->pivot->order);

        // assert the image exists on both locations
        $this->assertFileExists(public_path($this->testArticle->imageurl));
        $this->assertFileExists(public_path($this->testArticle->getFileUrl()));
    }

    /** @test */
    public function it_can_migrate_images_without_order()
    {
        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);

        $article = Article::first();

        // assert the image exists on both locations
        $this->assertFileExists(public_path($article->imageurl));
        $this->assertFileExists(public_path($article->getFileUrl()));
    }

    /** @test */
    public function it_can_migrate_images_if_some_models_dont_exist()
    {
        $this->testArticle->save();

        Schema::create('test_media', function (Blueprint $table) {
            $table->string('imagepath')->nullable();
            $table->integer('productid')->nullable();
        });

        DB::table('test_media')->insert([
        ['productid' => $this->testArticle->id, 'imagepath' => '/uploads/warpaint-logo.svg'],
        ['productid' => 52, 'imagepath' => '/uploads/warpaint-logo.svg'],
    ]);

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                'table'       => 'test_media',
                'urlcolumn'   => 'imagepath',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
                'idcolumn'    => 'productid',
            ]);

        $article = Article::first();

        // assert the image exists on both locations
        $this->assertFileExists(public_path($article->imageurl));
        $this->assertFileExists(public_path($article->getFileUrl()));
    }

    /** @test */
    public function it_can_migrate_multiple_images_for_the_same_model()
    {
        copy(public_path('/uploads/warpaint-logo.svg'), public_path('/uploads/warpaint-logo-extra.svg'));

        $this->testArticle->save();

        Schema::create('test_media', function (Blueprint $table) {
            $table->string('imagepath')->nullable();
            $table->integer('productid')->nullable();
        });

        DB::table('test_media')->insert([
            ['productid' => $this->testArticle->id, 'imagepath' => '/uploads/warpaint-logo.svg'],
            ['productid' => $this->testArticle->id, 'imagepath' => '/uploads/warpaint-logo-extra.svg'],
        ]);

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                'table'       => 'test_media',
                'urlcolumn'   => 'imagepath',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
                'idcolumn'    => 'productid',
            ]);

        $this->assertCount(2, $this->testArticle->fresh()->assets);

        // assert the image exists on both locations
        $this->assertFileExists(public_path($this->testArticle->imageurl));
        $this->assertFileExists(public_path($this->testArticle->getFileUrl()));
    }

    /** @test */
    public function it_can_run_dry_to_migrate_images_to_assets()
    {
        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [
                '--dry'       => true,
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Tests\stubs\Article',
            ]);

        $article = Article::first();

        // assert the image exists on both locations
        $this->assertFileExists(public_path($article->imageurl));
        $this->assertEquals('', $article->getFileName());
        $this->assertCount(0, $article->assets);
    }
}
