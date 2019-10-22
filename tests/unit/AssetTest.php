<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Exceptions\ConfigException;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Models\AssetLibrary;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;
use Thinktomorrow\AssetLibrary\Models\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException;
use Thinktomorrow\AssetLibrary\Exceptions\CorruptMediaException;

class AssetTest extends TestCase
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
    public function it_can_attach_an_image_to_a_model()
    {
        $original = Article::create();

        app(AddAsset::class)->add($original, $this->getUploadedAsset());

        $this->assertCount(1, $original->assetRelation()->get());
    }

    /**
     * @test
     */
    public function it_can_get_all_the_media_files()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals('image.png', $asset->filename());
        $this->assertEquals('/media/1/image.png', $asset->url());

        $article = Article::create();

        //upload a single image
        app(AddAsset::class)->add($article, $this->getUploadedAsset('image.png'), 'banner', 'nl');

        $this->assertEquals('image.png', $article->asset('banner', 'nl')->filename());
        $this->assertEquals('/media/2/image.png', $article->asset('banner', 'nl')->url());

        app(AddAsset::class)->add($article, $this->getUploadedAsset('image.png'), 'thumbnail', 'fr');

        $this->assertEquals('image.png', $article->asset('thumbnail', 'fr')->filename());
        $this->assertEquals('/media/3/image.png', $article->asset('thumbnail', 'fr')->url());

        $this->assertEquals(3, AssetLibrary::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_can_create_conversions()
    {
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->filename(), 'image.png');
        $this->assertEquals($asset->url(), '/media/1/image.png');
        $this->assertEquals('/media/1/conversions/image-thumb.png', $asset->url('thumb'));
    }

    /**
     * @test
     */
    public function it_can_return_the_url_for_pdf_or_xls()
    {
        $asset  = $this->getUploadedAsset('foobar.pdf');
        $asset1 = $this->getUploadedAsset('foobar.xls');

        $this->assertEquals($asset->filename(), 'foobar.pdf');
        $this->assertEquals($asset->url(), '/media/1/foobar.pdf');

        $this->assertEquals($asset1->filename(), 'foobar.xls');
        $this->assertEquals($asset1->url(), '/media/2/foobar.xls');
    }

    /**
     * @test
     */
    public function it_can_get_its_mimetype()
    {
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getMimeType(), 'image/png');
    }

    /**
     * This test fails locally but succeeds in our CI pipeline.
     *
     * @test
     */
    public function it_can_get_its_size()
    {
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getSize(), '109 B');
    }

    /**
     * @test
     */
    public function it_can_get_its_dimensions()
    {
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getDimensions(), '100 x 100');
    }

    /**
     * @test
     */
    public function it_can_check_if_it_has_a_file()
    {
        $asset = $this->getUploadedAsset();

        $this->assertTrue($asset->hasFile());
    }

    /**
     * @test
     */
    public function it_returns_an_empty_string_if_there_is_no_media()
    {
        $asset = new Asset;

        $this->assertEquals('', $asset->getMimeType());
        $this->assertEquals('', $asset->getSize());
        $this->assertEquals('', $asset->getDimensions());
    }

    /**
     * @test
     */
    public function it_can_get_the_extensions_for_filtering()
    {
        $asset  =  $this->getUploadedAsset();
        $asset1 =  $this->getUploadedAsset('foobar.pdf');
        $asset2 =  $this->getUploadedAsset('foobar.xls');
        $asset3 =  $this->getUploadedAsset('image.test');

        $this->assertEquals('image', $asset->getExtensionForFilter());
        $this->assertEquals('pdf', $asset1->getExtensionForFilter());
        $this->assertEquals('xls', $asset2->getExtensionForFilter());
        $this->assertEquals('', $asset3->getExtensionForFilter());
    }

    /**
     * @test
     */
    public function it_will_keep_the_extension_after_upload()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.jpeg', 100, 100));

        $this->assertEquals('/media/1/conversions/image-thumb.jpeg', $asset->url('thumb'));
    }

    /**
     * @test
     */
    public function it_can_crop_an_image()
    {
        config(['assetlibrary.allowCropping' => true]);
        $asset = $this->getUploadedAsset('image.png', 1000, 1000)->crop(600, 400, 60, 100);

        $this->assertEquals('/media/1/conversions/image-cropped.png', $asset->url('cropped'));
        $this->assertEquals('600 x 400', $asset->getDimensions('cropped'));
    }

    /**
     * @test
     */
    public function it_can_not_crop_an_image_if_the_setting_is_turned_off()
    {
        $this->expectException(ConfigException::class);

        config(['assetlibrary.allowCropping' => false]);
        $asset = $this->getUploadedAsset('image.png', 1000, 1000)->crop(600, 400, 60, 100);

        $this->assertEquals('1000 x 1000', $asset->getDimensions('cropped'));
    }

    /**
     * @test
     */
    public function it_can_set_the_order()
    {
        $original = Article::create();

        $asset = $this->getUploadedAsset();
        app(AddAsset::class)->setOrder(6)->add($original, $asset);

        $this->assertEquals($asset->id, $original->fresh()->assetRelation->where('pivot.order', 6)->first()->id);
    }

    /**
     * @test
     */
    public function it_can_get_files_in_order()
    {
        $original = Article::create();

        $asset1 = $this->getUploadedAsset();
        app(AddAsset::class)->setOrder(2)->add($original, $asset1);

        $asset2 = $this->getUploadedAsset('image.png');
        app(AddAsset::class)->setOrder(1)->add($original, $asset2);

        $this->assertEquals($asset2->id, $original->assetRelation->first()->id);
        $this->assertEquals($asset1->id, $original->assetRelation->where('pivot.order', 2)->last()->id);
    }

    /**
     * @test
     */
    public function it_throws_an_expection_when_adding_an_existing_asset()
    {
        $this->expectException(AssetUploadException::class);

        $original = Article::create();

        //upload a single image
        $asset   = $this->getUploadedAsset();

        app(AddAsset::class)->add($original->fresh(), $asset);
        app(AddAsset::class)->add($original, $original->assetRelation()->first());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_no_media_is_attached_to_an_asset()
    {
        $this->expectException(CorruptMediaException::class);
        $this->expectExceptionMessage('There seems to be something wrong with asset id 1. There is no media attached at this time.');

        //upload a single image
        $asset = $this->getUploadedAsset();

        $asset->media->first()->delete();

        $asset->fresh()->url();
    }

    /**
     * @test
     */
    public function it_can_remove_itself()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->filename(), 'image.png');
        $this->assertEquals($asset->url(), '/media/1/image.png');
        $this->assertFileExists(public_path($asset->url()));

        $filepath = $asset->url();
        $asset->delete();

        $this->assertFileNotExists(public_path($filepath));
        $this->assertCount(0, Asset::all());
    }

    /** @test */
    public function it_throws_an_error_if_no_media_is_attached_to_an_asset_on_extension_filter()
    {
        $this->expectException(CorruptMediaException::class);
        $this->expectExceptionMessage('There seems to be something wrong with asset id 1. There is no media attached at this time.');

        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $asset->media->first()->delete();

        $asset->fresh()->getExtensionForFilter();
    }
}
