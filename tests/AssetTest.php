<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;
use Thinktomorrow\AssetLibrary\Test\stubs\Article;

class AssetTest extends TestCase
{
    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
        parent::tearDown();
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
    public function it_can_upload_an_image_to_a_model()
    {
        $original = Article::create();

        //upload a single image
        $article = AssetUploader::upload(UploadedFile::fake()->image('image.png'))->attachToModel($original);

        $this->assertEquals('image.png', $article->getFilename());
        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
        $this->assertEquals($original->assets()->first()->getFilename(), $article->getFilename());

        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $this->assertEquals('image.png', $asset->getFilename());
        $this->assertEquals('/media/2/image.png', $asset->getImageUrl());
    }

    /**
     * @test
     */
    public function it_can_get_all_the_media_files()
    {
        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $this->assertEquals('image.png', $asset->getFilename());
        $this->assertEquals('/media/1/image.png', $asset->getFileUrl());

        $article = Article::create();

        //upload a single image
        $article = AssetUploader::upload(UploadedFile::fake()->image('image2.png'))->attachToModel($article, 'banner', 'nl');

        $this->assertEquals('image2.png', $article->getFilename('banner', 'nl'));
        $this->assertEquals('/media/2/image2.png', $article->getFileUrl('banner', '', 'nl'));

        $article->addFile(UploadedFile::fake()->image('image3.png'), 'thumbnail', 'fr');

        $this->assertEquals('image3.png', $article->getFilename('thumbnail', 'fr'));
        $this->assertEquals('/media/3/image3.png', $article->getFileUrl('thumbnail', '', 'fr'));

        $this->assertEquals(3, Asset::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_can_remove_an_image()
    {
        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        $asset2 = AssetUploader::upload(UploadedFile::fake()->image('image2.png'));

        $this->assertEquals($asset2->getFilename(), 'image2.png');
        $this->assertEquals($asset2->getImageUrl(), '/media/2/image2.png');

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
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

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
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        $asset2 = AssetUploader::upload(UploadedFile::fake()->image('image2.png'));

        $this->assertEquals($asset2->getFilename(), 'image2.png');
        $this->assertEquals($asset2->getImageUrl(), '/media/2/image2.png');

        Asset::remove([$asset->id, $asset2->id]);

        $this->assertEquals(0, Asset::getAllAssets()->count());
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
    public function it_can_create_conversions()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');
        $this->assertEquals('/media/1/conversions/thumb.png', $asset->getFileUrl('thumb'));
    }

    /**
     * @test
     */
    public function it_can_return_the_url_for_pdf_or_xls()
    {
        $images = [UploadedFile::fake()->create('foobar.pdf'), UploadedFile::fake()->create('foobar.xls')];

        $asset = AssetUploader::upload($images);

        $this->assertEquals($asset[0]->getFilename(), 'foobar.pdf');
        $this->assertEquals($asset[0]->getFileUrl(), '/media/1/foobar.pdf');

        $this->assertEquals($asset[1]->getFilename(), 'foobar.xls');
        $this->assertEquals($asset[1]->getFileUrl(), '/media/2/foobar.xls');
    }

    /**
     * @test
     */
    public function it_can_get_the_image_url()
    {
        $files = [UploadedFile::fake()->create('foobar.pdf'), UploadedFile::fake()->create('foobar.xls'), UploadedFile::fake()->image('image.mp4')];

        $asset = AssetUploader::upload($files);

        $this->assertEquals($asset[0]->getFilename(), 'foobar.pdf');
        $this->assertEquals(asset('assets/back/img/pdf.png'), $asset[0]->getImageUrl());

        $this->assertEquals($asset[1]->getFilename(), 'foobar.xls');
        $this->assertEquals(asset('assets/back/img/xls.png'), $asset[1]->getImageUrl());

        $this->assertEquals($asset[2]->getFilename(), 'image.mp4');
        $this->assertEquals(asset('assets/back/img/other.png'), $asset[2]->getImageUrl());
    }

    /**
     * @test
     */
    public function it_can_get_its_mimetype()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $this->assertEquals($asset->getMimeType(), 'image/png');
    }

    /**
     * @test
     */
    public function it_can_get_its_size()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $this->assertEquals($asset->getSize(), '70 B');
    }

    /**
     * @test
     */
    public function it_can_get_its_dimensions()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

        $this->assertEquals($asset->getDimensions(), '100 x 100');
    }

    /**
     * @test
     */
    public function it_can_check_if_it_has_a_file()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

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
    public function it_can_attach_an_asset_instead_of_a_file()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $asset2 = AssetUploader::upload($asset);

        $this->assertEquals('/media/1/image.png', $asset2->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_to_multiple_models()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        $asset2 = AssetUploader::upload($asset);

        $this->assertEquals('/media/1/image.png', $asset->getFileUrl());
        $this->assertEquals('/media/1/image.png', $asset2->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_get_the_extensions_for_filtering()
    {
        //TODO uncomment these when we can supply the mimetype to UploadedFile
        $asset  =  AssetUploader::upload(UploadedFile::fake()->image('image.png'));
//        $asset1 =  AssetUploader::upload(UploadedFile::fake()->create('image.pdf'));
//        $asset2 =  AssetUploader::upload(UploadedFile::fake()->create('image.xls'));
        $asset3 =  AssetUploader::upload(UploadedFile::fake()->create('image.test'));

        $this->assertEquals('image', $asset->getExtensionForFilter());
//        $this->assertEquals('pdf', $asset1->getExtensionForFilter());
//        $this->assertEquals('excel', $asset2->getExtensionForFilter());
        $this->assertEquals('', $asset3->getExtensionForFilter());
    }

    /**
     * @test
     */
    public function it_can_prefix_the_conversions_with_the_filename()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        config(['assetlibrary.conversionPrefix' => true]);

        $this->assertEquals('/media/1/conversions/image_thumb.png', $asset->getFileUrl('thumb'));
    }

    /**
     * @test
     */
    public function it_can_prefix_the_conversions_with_the_filename_and_get_the_orginal()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));

        config(['assetlibrary.conversionPrefix' => true]);

        $this->assertEquals('/media/1/image.png', $asset->getFileUrl());
    }

    /**
     * @test
     */
    public function it_will_keep_the_extension_after_upload()
    {
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.jpg', 100, 100));

        $this->assertEquals('/media/1/conversions/thumb.jpg', $asset->getFileUrl('thumb'));
    }

    /**
     * @test
     */
    public function it_can_crop_an_image()
    {
        config(['assetlibrary.allowCropping' => true]);
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.jpg', 1000, 1000))->crop(600, 400, 60, 100);

        $this->assertEquals('/media/1/conversions/cropped.jpg', $asset->getFileUrl('cropped'));
        $this->assertEquals('600 x 400', $asset->getDimensions('cropped'));
    }

    /**
     * @test
     */
    public function it_can_not_crop_an_image_if_the_setting_is_turned_off()
    {
        $this->expectExceptionMessage("The cropping config setting needs to be turned on to crop images. See 'Config\assetlibrary.php' for the 'allowCropping' field.");
        config(['assetlibrary.allowCropping' => false]);
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.jpg', 1000, 1000))->crop(600, 400, 60, 100);

        $this->assertEquals('1000 x 1000', $asset->getDimensions('cropped'));
    }

    /**
    * @test
    */
    public function it_can_set_the_order(){
        $original = Article::create();

        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.jpg', 1000, 1000));

        $asset->setOrder(6)->attachToModel($original);

        $this->assertEquals($asset->id, $original->assets->where('pivot.order', 6)->first()->id);
    }

    /**
    * @test
    */
    public function it_can_get_files_in_order(){
        $original = Article::create();

        $asset1 = AssetUploader::upload(UploadedFile::fake()->image('image.jpg', 1000, 1000));
        $asset1->setOrder(2)->attachToModel($original);

        $asset2 = AssetUploader::upload(UploadedFile::fake()->image('image1.jpg', 1000, 1000));
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
    public function it_throws_an_expection_when_adding_an_existing_asset(){

        $this->expectException(AssetUploadException::class);

        $original = Article::create();

        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));
        $article = $asset->attachToModel($original);

        $article->addFile($article->assets()->first());

    }
    
    /**
    * @test
    */
    public function it_doesnt_remove_the_asset_if_you_dont_have_permissions(){
        //upload a single image
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));
        $dir = public_path($asset->getFileUrl());

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
}
