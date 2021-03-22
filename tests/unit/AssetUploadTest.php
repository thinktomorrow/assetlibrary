<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\AssetUploader;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetUploadTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function tearDown(): void
    {
        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /**
     * @test
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
        $this->assertEquals('testsource-1.png', $asset->filename());
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

        $this->assertEquals(3, Asset::all()->count());
    }

    /**
     * @test
     */
    public function it_can_upload_multiple_images_with_the_same_type()
    {
        $original = Article::create();

        //upload a single image
        app(AddAsset::class)->add($original, AssetUploader::upload(UploadedFile::fake()->image('image.png')), 'images', 'en');

        //upload a second single image
        app(AddAsset::class)->add($original, AssetUploader::upload(UploadedFile::fake()->image('image.png')), 'images', 'en');

        $this->assertCount(2, $original->assets('images'));
    }

    /**
     * @test
     */
    public function it_can_upload_an_image()
    {
        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));
        $this->assertEquals('image.png', $asset->filename());
        $this->assertEquals('/media/1/image.png', $asset->url());

        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image2.png'));

        $this->assertEquals('image2.png', $asset->filename());
        $this->assertEquals('/media/2/image2.png', $asset->url());
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_uploading_an_invalid_file()
    {
        $this->expectException(InvalidArgumentException::class);
        //upload a single image
        AssetUploader::upload(5);
    }

    /** @test */
    public function it_throws_an_error_when_passing_null_instead_of_a_file()
    {
        $this->expectException(InvalidArgumentException::class);
        //upload a single image

        AssetUploader::uploadToAsset(null, Asset::create());
    }

    /**
     * @test
     */
    public function it_can_upload_multiple_images()
    {
        //upload multiple images
        $images = [UploadedFile::fake()->image('image.png'), UploadedFile::fake()->image('image2.png')];

        $asset = AssetUploader::upload($images);

        $this->assertEquals($asset[0]->filename(), 'image.png');
        $this->assertEquals($asset[0]->url(), '/media/1/image.png');

        $this->assertEquals($asset[1]->filename(), 'image2.png');
        $this->assertEquals($asset[1]->url(), '/media/2/image2.png');
    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_instead_of_a_file()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $asset2 = AssetUploader::upload($asset);

        $this->assertEquals('/media/1/image.png', $asset2->url());
    }

    /**
     * @test
     */
    public function it_will_keep_the_extension_after_upload()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.jpg', 100, 100));

        $this->assertEquals('/media/1/conversions/image-thumb.jpg', $asset->url('thumb'));
    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_to_multiple_models()
    {
        $asset = $this->getUploadedAsset();

        $asset2 = AssetUploader::upload($asset);

        $this->assertEquals('/media/1/image.png', $asset->url());
        $this->assertEquals('/media/1/image.png', $asset2->url());
    }

    /**
     * @test
     */
    public function it_can_upload_an_asset_to_different_disk()
    {
        $asset = AssetUploader::upload(
            UploadedFile::fake()->image('image.png'),
            'testname.jpg',
            'default', // no way to change collection via assets for now...
            'local'
        );

        $this->assertStringEndsWith('storage/app/1/testname.jpg', $asset->media->first()->getPath());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_file_is_null()
    {
        $this->expectException(InvalidArgumentException::class);

        AssetUploader::upload(null);
    }
}
