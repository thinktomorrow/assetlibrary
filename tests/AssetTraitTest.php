<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Test\stubs\Article;

class AssetTraitTest extends TestCase
{
    public function tearDown()
    {
        Artisan::call('medialibrary:clear');
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        parent::tearDown();
    }
    /**
     * @test
     */
    public function it_can_get_a_file_url_without_a_type()
    {
        $article = Article::create();

        Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_a_type()
    {
        $article = Article::create();

        $article = Asset::upload(UploadedFile::fake()->image('bannerImage.png'))->attachToModel($article, 'banner');
        $article = Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article);

        $this->assertEquals('/media/1/bannerImage.png', $article->getFileUrl('banner'));
        $this->assertEquals('/media/2/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_a_type_and_size()
    {
        $article = Article::create();

        Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article, 'banner');

        $this->assertEquals('/media/1/conversions/thumb.png', $article->getFileUrl('banner', 'thumb'));
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_type_for_locale()
    {
        $article = Article::create();
        config(['app.locale' => 'nl']);

        Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article, 'banner');
        $article->addFile(UploadedFile::fake()->image('imageFR.png'), 'banner', 'fr');

        $this->assertEquals('/media/1/image.png', $article->getFileUrl('banner', '', 'nl'));
        $this->assertEquals('/media/2/imageFR.png', $article->getFileUrl('banner', '', 'fr'));
    }

    /**
     * @test
     */
    public function it_can_get_a_file_url_with_all_variables()
    {
        $article = Article::create();

        Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article, 'banner', 'nl');
        $article->addFile(UploadedFile::fake()->image('imageFR.png'), 'thumbnail', 'fr');

        $this->assertEquals('/media/1/conversions/large.png', $article->getFileUrl('banner', 'large', 'nl'));
        $this->assertEquals('/media/2/conversions/thumb.png', $article->getFileUrl('thumbnail', 'thumb', 'fr'));
    }

    /**
     * @test
     */
    public function it_can_get_the_default_locale_if_the_translation_does_not_exist()
    {
        $article = Article::create();

        Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article, 'banner', 'nl');

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

        $article = Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article);

        $this->assertTrue($article->hasFile());
    }

    /**
     * @test
     */
    public function it_can_check_if_it_has_a_file_with_a_type()
    {
        $article = Article::create();

        $this->assertFalse($article->hasFile('banner'));

        Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article,'banner');

        $this->assertTrue($article->hasFile('banner'));
    }

    /**
     * @test
     */
    public function it_can_add_a_file_translation()
    {
        $article = Article::create();
        config(['app.locale' => 'nl']);
        $article->addFile(UploadedFile::fake()->image('image.png'),'banner','nl');
        $article->addFile(UploadedFile::fake()->image('imagefr.png'),'banner','fr');

        $this->assertTrue($article->hasFile('banner'));
        $this->assertTrue($article->hasFile('banner', 'fr'));
        $this->assertFalse($article->hasFile('banner', 'en'));
    }

    /**
     * @test
     */
    public function it_can_add_a_file_translation_for_default_locale()
    {
        $article = Article::create();
        $article->addFile(UploadedFile::fake()->image('image.png'),'banner');
        $article->addFile(UploadedFile::fake()->image('imagefr.png'),'banner','fr');

        $this->assertTrue($article->hasFile('banner'));
        $this->assertTrue($article->hasFile('banner', 'fr'));

    }

    /**
     * @test
     */
    public function it_can_replace_a_translation()
    {
        $article = Article::create();
        $article->addFile(UploadedFile::fake()->image('image.png'), 'banner');
        $article->addFile(UploadedFile::fake()->image('imageNL.png'), 'banner');

        $this->assertEquals('/media/2/imageNL.png',$article->getFileUrl('banner'));

    }

    /**
     * @test
     */
    public function it_can_attach_an_asset_if_it_is_given_instead_of_a_file()
    {
        $article = Article::create();
        $asset = Asset::upload(UploadedFile::fake()->image('image.png', 100, 100));

        $article->addFile($asset);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_multiple_assets()
    {
        $article = Article::create();
        $assets[] = Asset::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = Asset::upload(UploadedFile::fake()->image('image.png', 100, 100));

        $article->addFile($assets);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
     */
    public function it_can_attach_multiple_assets_and_files()
    {
        $article = Article::create();
        $assets[] = Asset::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = Asset::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $assets[] = UploadedFile::fake()->image('image.png');

        $article->addFile($assets);

        $this->assertEquals('/media/1/image.png', $article->getFileUrl());
    }

    /**
     * @test
    */
    public function it_can_attach_an_asset_to_multiple_models()
    {
        $article    = Article::create();
        $article2   = Article::create();
        $asset      = Asset::upload(UploadedFile::fake()->image('image.png', 100, 100));
        $asset->attachToModel($article, 'banner');

        $article2->addFile($asset, 'banner');

        $this->assertEquals('/media/1/conversions/thumb.png', $article->getFileUrl('banner', 'thumb'));
        $this->assertEquals('/media/1/conversions/thumb.png', $article2->getFileUrl('banner', 'thumb'));
    }

    /**
     * @test
     */
    public function it_can_change_an_image_connected_to_multiple_models_without_changing_the_other_models()
    {
        $article    = Article::create();
        $article2   = Article::create();
        $asset      = Asset::upload(UploadedFile::fake()->image('image.png', 100, 100));
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

        $article = Asset::upload(UploadedFile::fake()->image('bannerImage.png'))->attachToModel($article, 'banner');
        $article = Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article, 'foo');
        $article = Asset::upload(UploadedFile::fake()->image('image.png'))->attachToModel($article, 'bar');
        $article = Asset::upload(UploadedFile::fake()->create('not-an-image.pdf'))->attachToModel($article, 'fail');

        $this->assertEquals(3, $article->getAllImages()->count());
    }



    /**
    * @test
    */
    public function it_can_upload_multiple_files()
    {
        //upload multiple images
        $images = [UploadedFile::fake()->image('image.png'), UploadedFile::fake()->image('image2.png')];

        $article = Article::create();
        config(['app.locale' => 'nl']);

        $article->addFile($images, '', 'nl');

        $this->assertEquals(2, $article->getAllFiles()->count());
    }

}
