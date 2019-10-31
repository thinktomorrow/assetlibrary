<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;

class AssetTraitTest extends TestCase
{
    private $base64Image = 'data:image/gif;base64,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';

<<<<<<< HEAD:tests/unit/AssetTraitTest.php
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
        Article::migrate();

        config(['app.locale' => 'xxx']);
        config(['app.fallback_locale' => 'nl']);
    }

    public function tearDown()
=======
    public function tearDown(): void
>>>>>>> master:tests/AssetTraitTest.php
    {
        Artisan::call('medialibrary:clear');
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_without_a_type()
    {
        $this->assertEquals('/media/1/image.png', $this->getArticleWithAsset()->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_a_type()
    {
        $this->assertEquals('/media/1/image.png', $this->getArticleWithAsset('banner')->getFileUrl('banner'));
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_a_type_and_size()
    {
        $this->assertEquals('/media/1/conversions/image-thumb.png', $this->getArticleWithAsset('banner')->getFileUrl('banner', 'thumb'));
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_type_for_locale()
    {
        $article = $this->getArticleWithAsset('banner');

        $article->addFile(UploadedFile::fake()->image('imageFR.png'), 'banner', 'fr');

        $this->assertEquals('/media/1/image.png', $article->getFileUrl('banner', '', 'nl'));
        $this->assertEquals('/media/2/imagefr.png', $article->getFileUrl('banner', '', 'fr'));
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_all_variables()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');
        $article->addFile(UploadedFile::fake()->image('imageFR.png'), 'thumbnail', 'fr');

        $this->assertEquals('/media/1/conversions/image-large.png', $article->getFileUrl('banner', 'large', 'nl'));
        $this->assertEquals('/media/2/conversions/imagefr-thumb.png', $article->getFileUrl('thumbnail', 'thumb', 'fr'));
    }

    /**
     * @test
     */
    public function it_can_get_the_fallback_locale_if_no_locale_is_passed()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');

        $this->assertEquals('/media/1/image.png', $article->getFileUrl('banner', '', 'nl'));

        $this->assertEquals('/media/1/image.png', $article->getFileUrl('banner', '', 'fr'));
    }

    /**
     * @test
     */
    public function it_can_check_if_it_has_a_file_without_a_type()
    {
        $article = Article::create();

        $this->assertFalse($article->hasFile());

        $article = $this->getArticleWithAsset();

        $this->assertTrue($article->hasFile());
    }

    /**
     * @test
     */
    public function it_can_check_if_it_has_a_file_with_a_type()
    {
        $article = Article::create();

        $this->assertFalse($article->hasFile('banner'));

        $article = $this->getArticleWithAsset('banner');

        $this->assertTrue($article->hasFile('banner'));
    }

    /**
     * @test
     */
    public function it_can_add_a_file_translation()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');
        $article->addFile(UploadedFile::fake()->image('imagefr.png'), 'banner', 'fr');

        $this->assertTrue($article->hasFile('banner'));
        $this->assertTrue($article->hasFile('banner', 'fr'));
        $this->assertFalse($article->hasFile('banner', 'en'));
    }

    /**
     * @test
     */
    public function it_can_add_a_file_translation_for_default_locale()
    {
        $article = $this->getArticleWithAsset('banner');
        $article->addFile(UploadedFile::fake()->image('imagefr.png'), 'banner', 'fr');

        $this->assertTrue($article->hasFile('banner'));
        $this->assertTrue($article->hasFile('banner', 'fr'));
    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_if_it_is_given_instead_of_a_file()
    {
        $article = $this->getArticleWithAsset();

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_multiple_assets()
    {
        $article  = Article::create();
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

        $article->addFiles($assets);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_array_of_assets_with_the_add_file_method()
    {
        $article  = Article::create();
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

        $article->addFile($assets);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_collection_of_assets_with_the_add_file_method()
    {
        $article    = Article::create();
        $assets     = collect([AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100)), AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100))]);

        $article->addFile($assets);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_multiple_assets_and_files()
    {
        $article  = Article::create();

        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = UploadedFile::fake()->image('image.png');

        $article->addFiles($assets);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_to_multiple_models()
    {
        $article    = Article::create();
        $article2   = Article::create();
        $asset      = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));

        $asset->attachToModel($article, 'banner');

        $article2->addFile($asset, 'banner');

        $this->assertEquals('/media/1/conversions/image-thumb.png', $article->getFileUrl('banner', 'thumb'));
        $this->assertEquals('/media/1/conversions/image-thumb.png', $article2->getFileUrl('banner', 'thumb'));
    }

    /**
     * @test
     */
    public function it_can_change_an_image_connected_to_multiple_models_without_changing_the_other_models()
    {
        $this->markTestIncomplete();

        $article    = Article::create();
        $article2   = Article::create();
        $asset      = AssetUploader::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $asset->attachToModel($article, 'banner');

        $article2->addFile($asset, 'banner');
        $article->addFile(UploadedFile::fake()->image('image2.png', 100, 100), 'banner');

        $this->assertEquals('/media/2/image2.png', $article->getFileUrl('banner'));
        $this->assertEquals('/media/1/image.png', $article2->getFileUrl('banner'));
    }

    /**
     * @test
     */
    public function it_can_get_all_the_images()
    {
        $article = Article::create();

        $asset = AssetUploader::upload(UploadedFile::fake()->image('bannerImage.png'));
        $asset->setOrder(50)->attachToModel($article, 'banner');
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.png'));
        $asset->setOrder(9)->attachToModel($article, 'foo');
        $asset = AssetUploader::upload(UploadedFile::fake()->image('image.PNG'));
        $asset->setOrder(40)->attachToModel($article, 'bar');
        $article = AssetUploader::upload(UploadedFile::fake()->create('not-an-image.pdf'))->attachToModel($article, 'fail');

        $this->assertCount(3, $article->getAllImages());
        $this->assertEquals(null, $article->assets->first()->pivot->order);
        $this->assertEquals(9, $article->assets->get('1')->pivot->order);
        $this->assertEquals(50, $article->assets->last()->pivot->order);
    }

    /**
     * @test
     */
    public function it_can_upload_multiple_files()
    {
        //upload multiple images
        $images = [UploadedFile::fake()->image('image.png'), UploadedFile::fake()->image('image2.png')];

        $article = Article::create();

        $article->addFiles($images, '', 'nl');

        $this->assertEquals(2, $article->getAllFiles()->count());
    }

    /**
     * @test
     */
    public function it_can_retrieve_all_files_regardless_of_type()
    {
        $images = [UploadedFile::fake()->image('image.png'), UploadedFile::fake()->image('image2.png')];

        $article = Article::create();

        $article->addFile($images[0], 'first-type');
        $article->addFile($images[1], 'second-type');

        $this->assertCount(2, $article->getAllFiles());
        $this->assertCount(1, $article->getAllFiles('first-type'));
        $this->assertCount(1, $article->getAllFiles('second-type'));
    }

    /**
     * @test
     */
    public function it_can_remove_an_asset()
    {
        $article = $this->getArticleWithAsset();

        $this->assertCount(1, $article->getAllFiles());

        $article->deleteAsset($article->assets->first()->id);

        $this->assertCount(0, Article::first()->getAllFiles());
    }

    /**
     * @test
     */
    public function it_can_replace_an_asset()
    {
        $article = $this->getArticleWithAsset();

        $this->assertCount(1, $article->getAllFiles());

        $article->replaceAsset($article->assets->first()->id, AssetUploader::upload(UploadedFile::fake()->image('newImage.png'))->id);

        $this->assertCount(1, $article->getAllFiles());
        $this->assertEquals('/media/2/newimage.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_replace_an_asset_with_specific_type()
    {
        $article = $this->getArticleWithAsset('custom-type');

        $this->assertCount(1, $assets = $article->getAllFiles('custom-type'));
        $article->replaceAsset($assets->first()->id, AssetUploader::upload(UploadedFile::fake()->image('newImage.png'))->id);

        $this->assertCount(1, $article->getAllFiles('custom-type'));
        $this->assertEquals('/media/2/newimage.png', $article->getFileUrl('custom-type'));
    }

    /**
     * @test
     */
    public function it_can_upload_a_base64_file()
    {
        $article = Article::create();

        $article->addFile($this->base64Image);

        $this->assertStringEndsWith('.gif', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_set_a_name_when_uploading_a_base64_file()
    {
        $article = Article::create();

        $article->addFile($this->base64Image, '', '', 'testImage.png');

        $this->assertEquals('/media/1/testimage.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_set_a_name_when_uploading_a_base64_file_keeping_original()
    {
        $article = Article::create();

        $article->addFile($this->base64Image, '', '', 'testImage.png', true);

        $this->assertEquals('/media/1/testimage.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_set_a_name_when_uploading_a_file()
    {
        $article = Article::create();

        $article->addFile(UploadedFile::fake()->image('newImage.png'), '', '', 'testImage.png');

        $this->assertEquals('/media/1/testimage.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_upload_multiple_base64_files_with_names()
    {
        $article = Article::create();

        $article->addFiles([
            'testImage1.png' => $this->base64Image,
            'testImage2.png' => $this->base64Image,
        ]);

        $this->assertEquals('/media/1/testimage1.png', $article->getFileUrl());
        $this->assertEquals('/media/2/testimage2.png', $article->getAllFiles()->last()->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_upload_multiple_files_with_names()
    {
        $article = Article::create();

        $article->addFiles([
            'testImage1.png' => UploadedFile::fake()->image('newImage.png'),
            'testImage2.png' => UploadedFile::fake()->image('newImage.png'),
            ]);

        $this->assertEquals('/media/1/testimage1.png', $article->getFileUrl());
        $this->assertEquals('/media/2/testimage2.png', $article->getAllFiles()->last()->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_get_the_files_sorted()
    {
        config(['app.locale' => 'xxx']);
        config(['app.fallback_locale' => 'nl']);

        $article = Article::create();

        $asset1 = Asset::create();
        $asset1->setOrder(3)->attachToModel($article, 'banner');
        $asset2 = Asset::create();
        $asset2->setOrder(1)->attachToModel($article, 'banner');
        $asset3 = Asset::create();
        $asset3->setOrder(2)->attachToModel($article, 'banner');
        $article = Asset::create()->attachToModel($article, 'fail');

        $images = $article->getAllFiles('banner');

        $this->assertCount(3, $images);
        $this->assertEquals($asset1->id, $images->pop()->id);
        $this->assertEquals($asset3->id, $images->pop()->id);
        $this->assertEquals($asset2->id, $images->pop()->id);
    }

    /**
     * @test
     */
    public function it_can_sort_images()
    {
        $article = Article::create();

        $asset1 = Asset::create();
        $asset1->attachToModel($article, 'banner');
        $asset2 = Asset::create();
        $asset2->attachToModel($article, 'banner');
        $asset3 = Asset::create();
        $asset3->attachToModel($article, 'banner');
        $article = Asset::create()->attachToModel($article, 'fail');

        $article->sortFiles('banner', [(string) $asset3->id, (string) $asset1->id, (string) $asset2->id]);

        $images = $article->getAllFiles('banner');

        $this->assertCount(3, $images);
        $this->assertEquals($asset2->id, $images->pop()->id);
        $this->assertEquals($asset1->id, $images->pop()->id);
        $this->assertEquals($asset3->id, $images->pop()->id);
    }

    /**
     * @test
     */
    public function it_can_sort_images_with_specified_keys()
    {
        $article = Article::create();

        $asset1 = Asset::create();
        $asset1->attachToModel($article, 'banner');
        $asset2 = Asset::create();
        $asset2->attachToModel($article, 'banner');
        $asset3 = Asset::create();
        $asset3->attachToModel($article, 'banner');
        $article = Asset::create()->attachToModel($article, 'fail');

        $article->sortFiles('banner', [5 => (string) $asset3->id, 1 => (string) $asset1->id, 9 => (string) $asset2->id]);

        $images = $article->getAllFiles('banner');

        $this->assertCount(3, $images);
        $this->assertEquals($asset2->id, $images->pop()->id);
        $this->assertEquals($asset3->id, $images->pop()->id);
        $this->assertEquals($asset1->id, $images->pop()->id);
    }
}
