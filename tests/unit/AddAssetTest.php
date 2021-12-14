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

class AddAssetTest extends TestCase
{
    private $base64Image = 'data:image/gif;base64,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';

    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function tearDown(): void
    {
        Artisan::call('media-library:clear');

        parent::tearDown();
    }

    /** @test */
    public function it_can_attach_an_asset_if_it_is_given_instead_of_a_file()
    {
        // Create an article model and attach an Asset model
        $article = $this->getArticleWithAsset('xxx', 'nl');

        $this->assertEquals('/media/1/image.png', $article->asset('xxx', 'nl')->url());
    }

    /** @test */
    public function it_can_attach_collection_of_assets()
    {
        $article  = Article::create();
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

        app(AddAsset::class)->addMultiple($article, collect($assets), 'xxx', 'nl');

        $this->assertCount(2, $article->assets('xxx', 'nl'));
    }

    /** @test */
    public function it_can_attach_assets_in_sequence()
    {
        $article  = Article::create();
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

        app(AddAsset::class)->add($article, $assets[0], 'xxx', 'nl');
        app(AddAsset::class)->add($article, $assets[1], 'xxx', 'nl');

        $this->assertCount(2, $article->assets('xxx', 'nl'));
    }

    /** @test */
    public function it_can_upload_multiple_files()
    {
        // upload multiple images
        $images = collect([UploadedFile::fake()->image('image.png'), UploadedFile::fake()->image('image2.png')]);

        $article = Article::create();

        app(AddAsset::class)->addMultiple($article, $images, 'xxx', 'nl');

        $this->assertEquals(2, $article->assets('xxx', 'nl')->count());
    }

    /** @test */
    public function it_can_attach_a_combination_of_assets_and_files()
    {
        $article  = Article::create();

        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = UploadedFile::fake()->image('image.png');

        app(AddAsset::class)->addMultiple($article, collect($assets), 'xxx', 'nl');

        $this->assertCount(3, $article->assets('xxx', 'nl'));
    }

    /** @test */
    public function it_can_attach_an_asset_to_multiple_models()
    {
        $article    = Article::create();
        $article2   = Article::create();
        $asset      = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

        app(AddAsset::class)->add($article, $asset, 'banner', 'nl');
        app(AddAsset::class)->add($article2, $asset, 'banner', 'nl');

        $this->assertEquals($article->asset('banner', 'nl')->id, $article2->asset('banner', 'nl')->id);

        $this->assertEquals('/media/1/conversions/image-thumb.png', $article->asset('banner', 'nl')->url('thumb'));
        $this->assertEquals('/media/1/conversions/image-thumb.png', $article2->asset('banner', 'nl')->url('thumb'));
    }

    /** @test */
    public function it_can_add_files_per_type()
    {
        $images = [UploadedFile::fake()->image('image.png'), UploadedFile::fake()->image('image2.png')];

        $article = Article::create();

        app(AddAsset::class)->add($article, $images[0], 'first-type', 'nl');
        app(AddAsset::class)->add($article, $images[1], 'second-type', 'nl');

        $this->assertCount(2, $article->assetRelation()->get());
        $this->assertCount(1, $article->assets('first-type', 'nl'));
        $this->assertCount(1, $article->assets('second-type', 'nl'));
    }

    /** @test */
    public function it_can_upload_a_base64_file()
    {
        $article = Article::create();

        app(AddAsset::class)->add($article, $this->base64Image, 'xxx', 'nl');

        $this->assertStringEndsWith('.gif', $article->asset('xxx', 'nl')->url());
    }

    /** @test */
    public function it_can_set_a_name_when_uploading_a_base64_file()
    {
        $article = Article::create();

        app(AddAsset::class)->add($article, $this->base64Image, 'xxx', 'en', 'testImage.png');

        $this->assertEquals('/media/1/testimage.png', $article->asset('xxx')->url());
    }

    /** @test */
    public function it_can_set_a_name_when_uploading_a_base64_file_keeping_original()
    {
        $article = Article::create();

        app(AddAsset::class)->add($article, $this->base64Image, 'xxx', 'nl', 'testImage.png');

        $this->assertEquals('/media/1/testimage.png', $article->asset('xxx', 'nl')->url());
    }

    /** @test */
    public function it_can_set_a_name_when_uploading_a_file()
    {
        $article = Article::create();

        app(AddAsset::class)->add($article, UploadedFile::fake()->image('newImage.png'), 'xxx', 'nl', 'testImage.png');

        $this->assertEquals('/media/1/testimage.png', $article->asset('xxx', 'nl')->url());
    }

    /** @test */
    public function it_can_upload_multiple_base64_files_with_names()
    {
        $article = Article::create();

        app(AddAsset::class)->addMultiple($article, collect([
            'testImage1.png' => $this->base64Image,
            'testImage2.png' => $this->base64Image,
        ]), 'xxx', 'nl');

        $this->assertEquals('/media/1/testimage1.png', $article->asset('xxx', 'nl')->url());
        $this->assertEquals('/media/2/testimage2.png', $article->assets('xxx', 'nl')->last()->url());
    }

    /** @test */
    public function it_can_upload_multiple_files_with_names()
    {
        $article = Article::create();

        app(AddAsset::class)->addMultiple($article, collect([
            'testImage1.png' => UploadedFile::fake()->image('newImage.png'),
            'testImage2.png' => UploadedFile::fake()->image('newImage.png'),
        ]), 'xxx', 'nl');

        $this->assertEquals('/media/1/testimage1.png', $article->asset('xxx', 'nl')->url());
        $this->assertEquals('/media/2/testimage2.png', $article->assets('xxx', 'nl')->last()->url());
    }

    /** @test */
    public function it_has_no_problem_with_upper_case_extentions()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');

        $image_name = json_decode($this->getBase64WithName('test.PNG'))->output->name;
        app(AddAsset::class)->add($article, json_decode($this->getBase64WithName('test.PNG'))->output->image, 'thumbnail', 'xx', $image_name);

        $article->load('assetRelation');

        $this->assertCount(2, $article->assetRelation()->get());
        $this->assertEquals('test.png', $article->asset('thumbnail', 'xx')->filename());
    }

    /**
     * @test
     */
    public function it_can_add_multiple_of_the_same_asset()
    {
        $original = Article::create();

        //upload a single image
        $asset   = $this->getUploadedAsset();

        app(AddAsset::class)->add($original->fresh(), $asset, 'xxx', 'en');
        app(AddAsset::class)->add($original, $original->assetRelation()->first(), 'xxx', 'en');

        $this->assertCount(2, $original->assetRelation()->get());
    }

    /** @test */
    public function addFile_returns_asset()
    {
        $article = $this->getArticleWithAsset('banner');

        $asset = app(AddAsset::class)->add($article, UploadedFile::fake()->image('imageFR.png'), 'banner', 'fr');

        $this->assertInstanceOf(Asset::class, $asset);
    }

    /** @test */
    public function adding_empty_file_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $article = $this->getArticleWithAsset('banner');

        $asset = app(AddAsset::class)->add($article, null, 'banner', 'fr');

        $this->assertInstanceOf(Asset::class, $asset);
    }
}
