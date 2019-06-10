<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Models\AssetLibrary;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;

class AssetUploadTest extends TestCase
{
    public function tearDown(): void
    {
        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /**
     * @test
     *
     */
    public function it_can_keep_original_source()
    {
        $source = UploadedFile::fake()->create('testSource.txt');

        // Second parameter is flag to preserve original source file
        $asset = AssetUploader::upload($source, null, true);

        $this->assertFileExists($source->getPath());
    }

    /**
     * @test
     */
    public function it_can_sanitize_the_filename()
    {
        $source = UploadedFile::fake()->image('testSource (1).png');

        $asset = AssetUploader::upload($source);
        $this->assertEquals('testsource-1.png', $asset->getFilename());
    }

    /**
     * @test
     */
    public function it_can_upload_an_array_of_assets()
    {
        $assets = collect([]);

        $assets->push(AssetUploader::upload(UploadedFile::fake()->image('image1.png')));
        $assets->push(AssetUploader::upload(UploadedFile::fake()->image('image2.png')));
        $assets->push(UploadedFile::fake()->image('image2.png'));

        AssetUploader::upload($assets);

        $this->assertEquals(3, AssetLibrary::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_can_upload_multiple_images_with_the_same_type()
    {
        $original = Article::create();

        //upload a single image
        AssetUploader::upload(UploadedFile::fake()->image('image.png'))->attachToModel($original, 'images');

        //upload a second single image
        AssetUploader::upload(UploadedFile::fake()->image('image.png'))->attachToModel($original, 'images');

        $this->assertCount(2, $original->getAllFiles('images'));
    }

    /**
     * @test
     */
    public function it_can_upload_an_image()
    {
        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));
        $this->assertEquals('image.png', $asset->getFilename());
        $this->assertEquals('/media/1/image.png', $asset->getImageUrl());

        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image2.png'));

        $this->assertEquals('image2.png', $asset->getFilename());
        $this->assertEquals('/media/2/image2.png', $asset->getImageUrl());
    }

    /**
     * @test
     */
    public function it_returns_null_when_uploading_an_invalid_file()
    {
        //upload a single image
        $asset = AssetUploader::upload(5);

        $this->assertNull($asset);
    }

    /**
     * @test
     */
    public function it_can_upload_multiple_images()
    {
        //upload multiple images
        $images = [UploadedFile::fake()->image('image.png'), UploadedFile::fake()->image('image2.png')];

        $asset = AssetUploader::upload($images);

        $this->assertEquals($asset[0]->getFilename(), 'image.png');
        $this->assertEquals($asset[0]->getImageUrl(), '/media/1/image.png');

        $this->assertEquals($asset[1]->getFilename(), 'image2.png');
        $this->assertEquals($asset[1]->getImageUrl(), '/media/2/image2.png');
    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_instead_of_a_file()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $asset2 = AssetUploader::upload($asset);

        $this->assertEquals('/media/1/image.png', $asset2->getFileUrl());
    }

    /**
     * @test
     */
    public function it_will_keep_the_extension_after_upload()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.jpg', 100, 100));

        $this->assertEquals('/media/1/conversions/image-thumb.jpg', $asset->getFileUrl('thumb'));
    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_to_multiple_models()
    {
        $asset = $this->getUploadedAsset();

        $asset2 = AssetUploader::upload($asset);

        $this->assertEquals('/media/1/image.png', $asset->getFileUrl());
        $this->assertEquals('/media/1/image.png', $asset2->getFileUrl());
    }
}
