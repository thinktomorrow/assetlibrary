<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Test\stubs\Article;
use Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException;
use Thinktomorrow\AssetLibrary\Exceptions\CorruptMediaException;

class AssetTest extends TestCase
{
    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_attach_an_image_to_a_model()
    {
        $original = Article::create();

        //upload a single image
        $article = $this->getUploadedAsset()->attachToModel($original);

        $this->assertEquals('image.png', $article->getFilename());
        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
        $this->assertEquals($original->assets()->first()->getFilename(), $article->getFilename());
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals('image.png', $asset->getFilename());
        $this->assertEquals('/media/2/image.png', $asset->getImageUrl());
    }

    /**
     * @test
     */
    public function it_can_get_all_the_media_files()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals('image.png', $asset->getFilename());
        $this->assertEquals('/media/1/image.png', $asset->getFileUrl());

        $article = Article::create();

        //upload a single image
        $article = $this->getUploadedAsset('image.png')->attachToModel($article, 'banner', 'nl');

        $this->assertEquals('image.png', $article->getFilename('banner', 'nl'));
        $this->assertEquals('/media/2/image.png', $article->getFileUrl('banner', '', 'nl'));

        $article = $this->getUploadedAsset('image.png')->attachToModel($article, 'thumbnail', 'fr');

        $this->assertEquals('image.png', $article->getFilename('thumbnail', 'fr'));
        $this->assertEquals('/media/3/image.png', $article->getFileUrl('thumbnail', '', 'fr'));

        $this->assertEquals(3, Asset::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_can_remove_an_image()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        $asset2 = $this->getUploadedAsset('image.png');

        $this->assertEquals($asset2->getFilename(), 'image.png');
        $this->assertEquals($asset2->getImageUrl(), '/media/2/image.png');

        Asset::remove($asset->id);

        $this->assertEquals(1, Asset::getAllAssets()->count());
        $this->assertEquals($asset2->id, Asset::getAllAssets()->first()->id);
    }

    /**
     * @test
     */
    public function it_can_handle_invalid_inputs_to_remove_function()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        Asset::remove([null]);
        Asset::remove(null);

        $this->assertEquals(1, Asset::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_can_remove_multiple_images()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        $asset2 = $this->getUploadedAsset('image.png');

        $this->assertEquals($asset2->getFilename(), 'image.png');
        $this->assertEquals($asset2->getImageUrl(), '/media/2/image.png');

        Asset::remove([$asset->id, $asset2->id]);

        $this->assertEquals(0, Asset::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_can_create_conversions()
    {
        $asset = $this->getUploadedAsset();
        
        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');
        $this->assertEquals('/media/1/conversions/image-thumb.png', $asset->getFileUrl('thumb'));
    }

    /**
     * @test
     */
    public function it_can_return_the_url_for_pdf_or_xls()
    {
        $asset = $this->getUploadedAsset('foobar.pdf');
        $asset1 = $this->getUploadedAsset('foobar.xls');

        $this->assertEquals($asset->getFilename(), 'foobar.pdf');
        $this->assertEquals($asset->getFileUrl(), '/media/1/foobar.pdf');

        $this->assertEquals($asset1->getFilename(), 'foobar.xls');
        $this->assertEquals($asset1->getFileUrl(), '/media/2/foobar.xls');
    }

    /**
     * @test
     */
    public function it_can_get_the_image_url()
    {
        $asset = $this->getUploadedAsset('foobar.pdf');
        $asset1 = $this->getUploadedAsset('foobar.xls');
        $asset2 = $this->getUploadedAsset('foobar.mp4');

        $this->assertEquals($asset->getFilename(), 'foobar.pdf');
        $this->assertEquals(asset('assets/back/img/pdf.png'), $asset->getImageUrl());

        $this->assertEquals($asset1->getFilename(), 'foobar.xls');
        $this->assertEquals(asset('assets/back/img/xls.png'), $asset1->getImageUrl());

        $this->assertEquals($asset2->getFilename(), 'foobar.mp4');
        $this->assertEquals(asset('assets/back/img/other.png'), $asset2->getImageUrl());
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
     * This test fails locally but succeeds in our CI pipeline
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
        //TODO uncomment these when we can supply the mimetype to UploadedFile
        $asset  =  $this->getUploadedAsset();
        $asset3 =  $this->getUploadedAsset('image.test');

        $this->assertEquals('image', $asset->getExtensionForFilter());
//        $this->assertEquals('pdf', $asset1->getExtensionForFilter());
//        $this->assertEquals('excel', $asset2->getExtensionForFilter());
        $this->assertEquals('', $asset3->getExtensionForFilter());
    }

    /**
     * @test
     */
    public function it_can_crop_an_image()
    {
        config(['assetlibrary.allowCropping' => true]);
        $asset = $this->getUploadedAsset('image.png', 1000, 1000)->crop(600, 400, 60, 100);

        $this->assertEquals('/media/1/conversions/image-cropped.png', $asset->getFileUrl('cropped'));
        $this->assertEquals('600 x 400', $asset->getDimensions('cropped'));
    }

    /**
     * @test
     */
    public function it_can_not_crop_an_image_if_the_setting_is_turned_off()
    {
        $this->expectExceptionMessage("The cropping config setting needs to be turned on to crop images. See 'Config\assetlibrary.php' for the 'allowCropping' field.");
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

        $asset->setOrder(6)->attachToModel($original);

        $this->assertEquals($asset->id, $original->assets->where('pivot.order', 6)->first()->id);
    }

    /**
     * @test
     */
    public function it_can_get_files_in_order()
    {
        $original = Article::create();

        $asset1 = $this->getUploadedAsset();
        $asset1->setOrder(2)->attachToModel($original);

        $asset2 = $this->getUploadedAsset('image.png');
        $asset2->setOrder(1)->attachToModel($original);

        $this->assertEquals($asset2->id, $original->assets->first()->id);
        $this->assertEquals($asset1->id, $original->assets->where('pivot.order', 2)->last()->id);
    }

    /**
     * @test
     */
    public function it_can_get_a_fallback_image()
    {
        $asset = new Asset;
        $this->assertEquals('http://localhost/assets/back/img/other.png', $asset->getImageUrl());
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
        $article = $asset->attachToModel($original);

        $article->addFile($article->assets()->first());
    }

    /**
     * @test
     */
    public function it_doesnt_remove_the_asset_if_you_dont_have_permissions()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();
        $dir   = public_path($asset->getFileUrl());

        @chmod($dir, 0444);

        $this->assertFileExists($dir);
        $this->assertFileIsReadable($dir);
        $this->assertFileNotIsWritable($dir);

        Asset::remove($asset->id);

        $this->assertEquals(1, Asset::getAllAssets()->count());
        $this->assertCount(1, $asset->fresh()->media);

        @chmod($dir, 0777);
        Asset::remove($asset->id);
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

        $asset->fresh()->getFileUrl();
    }

    /**
     * @test
     */
    public function it_can_remove_itself()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');
        $this->assertFileExists(public_path($asset->getImageUrl()));

        $asset->removeSelf();

        $this->assertFileNotExists(public_path($asset->getImageUrl()));
        $this->assertCount(0, Asset::all());
    }
    
    public function it_throws_an_error_if_no_media_is_attached_to_an_asset_on_extension_filter(){

        $this->expectException(CorruptMediaException::class);
        $this->expectExceptionMessage("There seems to be something wrong with asset id 1. There is no media attached at this time.");

        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $asset->media->first()->delete();

        $asset->fresh()->getExtensionForFilter();
    }
}
