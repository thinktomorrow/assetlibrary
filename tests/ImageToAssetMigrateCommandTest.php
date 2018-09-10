<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Test\stubs\Article;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;
use Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException;
use Thinktomorrow\AssetLibrary\Exceptions\CorruptMediaException;

class ImageToAssetMigrateCommandTest extends TestCase
{
    public function tearDown()
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
        // create dummy table with image url
        Article::migrate();

        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [ 
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Test\stubs\Article'
            ]);

        // assert the image exists on both locations
        $this->assertFileExists(public_path(Article::first()->imageurl));
        $this->assertFileExists(public_path(Article::first()->getFileUrl()));
    }

    /** @test */
    public function it_can_migrate_multiple_images()
    {
        // create dummy table with image url
        Article::migrate();

        // fill db with an entry
        $this->testArticle->imageurl = '/uploads/warpaint-logo.svg';
        $this->testArticle->save();

        $article = Article::create();
        $article->imageurl = '/uploads/warpaint-logo.svg';
        $article->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [ 
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Test\stubs\Article'
            ]);
        // assert the image exists on both locations
        $this->assertFileExists(public_path($this->testArticle->fresh()->imageurl));
        $this->assertFileExists(public_path($article->fresh()->imageurl));
        $this->assertFileExists(public_path($this->testArticle->fresh()->getFileUrl()));
        $this->assertFileExists(public_path($article->fresh()->getFileUrl()));
    }

    /** @test */
    public function it_continues_if_an_image_doesnt_exist()
    {
        // create dummy table with image url
        Article::migrate();

        // fill db with an entry
        $this->testArticle->imageurl = 'foobar.svg';
        $this->testArticle->save();

        $article = Article::create();
        $article->imageurl = '/uploads/warpaint-logo.svg';
        $article->save();

        // run the migrate image command
        Artisan::call('assetlibrary:migrate-image', [ 
                'table'       => 'test_models',
                'urlcolumn'   => 'imageurl',
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Test\stubs\Article'
            ]);
        // assert the image exists on both locations
        $this->assertFileExists(public_path($article->fresh()->imageurl));
        $this->assertFileExists(public_path($article->fresh()->getFileUrl()));
    }

    /** @test */
    public function it_can_remove_the_original_image()
    {
        // create dummy table with image url
        Article::migrate();

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
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Test\stubs\Article'
            ]);
        // assert the image exists only on new location
        $this->assertFileNotExists(public_path($this->testArticle->fresh()->imageurl));
        $this->assertFileExists(public_path($this->testArticle->fresh()->getFileUrl()));
    }

     /** @test */
     public function it_can_remove_existing_assets_on_model()
     {
         // create dummy table with image url
        Article::migrate();

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
                'linkedmodel' => 'Thinktomorrow\AssetLibrary\Test\stubs\Article'
            ]);

        // assert the orginal asset was removed
        $this->assertCount(1, $this->testArticle->assets);

        // assert the image exists on both locations
        $this->assertFileExists(public_path($this->testArticle->fresh()->imageurl));
        $this->assertFileExists(public_path($this->testArticle->fresh()->getFileUrl()));
        $this->assertequals($this->testArticle->fresh()->getFileName(), 'warpaint-logo.svg');
     }
}
