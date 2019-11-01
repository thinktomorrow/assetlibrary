<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\AssetUploader;
use Thinktomorrow\AssetLibrary\Application\DeleteAsset;
use Thinktomorrow\AssetLibrary\Application\ReplaceAsset;
use Thinktomorrow\AssetLibrary\Application\SortAssets;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\stubs\ArticleWithSoftdelete;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetTraitTest extends TestCase
{
    private $base64Image = 'data:image/gif;base64,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';

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

    /** @test */
    public function it_can_get_a_file_url_with_a_type()
    {
        $this->assertEquals('/media/1/image.png', $this->getArticleWithAsset('banner')->asset('banner')->url());
    }

    /** @test */
    public function it_can_get_a_file_url_with_a_type_and_size()
    {
        $this->assertEquals('/media/1/conversions/image-thumb.png', $this->getArticleWithAsset('banner')->asset('banner')->url('thumb'));
    }

    /** @test */
    public function it_can_get_a_file_url_with_type_for_locale()
    {
        $article = $this->getArticleWithAsset('banner');

        app(AddAsset::class)->add($article, UploadedFile::fake()->image('imageFR.png'), 'banner', 'fr');

        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'en')->url());
        $this->assertEquals('/media/2/imagefr.png', $article->asset('banner', 'fr')->url());
    }

    /** @test */
    public function it_can_get_a_file_url_with_all_variables()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');
        app(AddAsset::class)->add($article, UploadedFile::fake()->image('imageFR.png'), 'thumbnail', 'fr');

        $this->assertEquals('/media/1/conversions/image-large.png', $article->asset('banner', 'nl')->url('large'));
        $this->assertEquals('/media/2/conversions/imagefr-thumb.png', $article->asset('thumbnail', 'fr')->url('thumb'));
    }

    /** @test */
    public function it_can_get_the_fallback_locale_if_no_locale_is_passed()
    {
        config()->set('thinktomorrow.assetlibrary.use_fallback_locale', true);
        config()->set('thinktomorrow.assetlibrary.fallback_locale', 'nl');
        $article = $this->getArticleWithAsset('banner', 'nl');

        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'nl')->url());
        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'fr')->url());
    }

    /** @test */
    public function it_can_check_if_it_has_a_file_with_a_type()
    {
        $article = Article::create();

        $this->assertNull($article->asset('banner'));

        $article = $this->getArticleWithAsset('banner');

        $this->assertNotNull($article->asset('banner'));
    }

    /** @test */
    public function it_can_remove_an_asset()
    {
        $article = $this->getArticleWithAsset('xxx');

        $this->assertCount(1, $article->assets('xxx'));

        app(DeleteAsset::class)->delete($article->assetRelation->first()->id);

        $this->assertCount(0, Article::first()->assets());
    }

    /** @test */
    public function it_can_replace_an_asset()
    {
        $article = $this->getArticleWithAsset('xxx');

        $this->assertCount(1, $article->assets('xxx'));

        app(ReplaceAsset::class)->handle($article, $article->assetRelation->first()->id, AssetUploader::upload(UploadedFile::fake()->image('newImage.png'))->id);

        $this->assertCount(1, $article->refresh()->assets('xxx'));
        $this->assertEquals('/media/2/newimage.png', $article->asset('xxx')->url());
    }

    /** @test */
    public function it_can_replace_an_asset_with_specific_type()
    {
        $article = $this->getArticleWithAsset('custom-type');

        $this->assertCount(1, $assets = $article->assets('custom-type'));
        app(ReplaceAsset::class)->handle($article, $assets->first()->id, AssetUploader::upload(UploadedFile::fake()->image('newImage.png'))->id);

        $this->assertCount(1, $article->refresh()->assets('custom-type'));
        $this->assertEquals('/media/2/newimage.png', $article->asset('custom-type')->url());
    }

    /** @test */
    public function it_can_sort_images()
    {
        $article = Article::create();

        $asset1 = Asset::create();
        app(AddAsset::class)->add($article, $asset1, 'banner', 'en');

        $asset2 = Asset::create();
        app(AddAsset::class)->add($article, $asset2, 'banner', 'en');

        $asset3 = Asset::create();
        app(AddAsset::class)->add($article, $asset3, 'banner', 'en');

        app(AddAsset::class)->add($article, Asset::create(), 'fail', 'en');


        app(SortAssets::class)->handle($article, 'banner', [(string) $asset3->id, (string) $asset1->id, (string) $asset2->id]);

        $images = $article->assets('banner');

        $this->assertCount(3, $images);
        $this->assertEquals($asset2->id, $images->pop()->id);
        $this->assertEquals($asset1->id, $images->pop()->id);
        $this->assertEquals($asset3->id, $images->pop()->id);
    }

    /** @test */
    public function it_can_sort_images_with_specified_keys()
    {
        $article = Article::create();

        $asset1 = Asset::create();
        app(AddAsset::class)->add($article, $asset1, 'banner', 'en');

        $asset2 = Asset::create();
        app(AddAsset::class)->add($article, $asset2, 'banner', 'en');

        $asset3 = Asset::create();
        app(AddAsset::class)->add($article, $asset3, 'banner', 'en');

        app(AddAsset::class)->add($article, Asset::create(), 'fail', 'en');

        app(SortAssets::class)->handle($article, 'banner', [5 => (string) $asset3->id, 1 => (string) $asset1->id, 9 => (string) $asset2->id]);

        $images = $article->assets('banner');

        $this->assertCount(3, $images);
        $this->assertEquals($asset2->id, $images->pop()->id);
        $this->assertEquals($asset3->id, $images->pop()->id);
        $this->assertEquals($asset1->id, $images->pop()->id);
    }

    /** @test */
    public function it_has_no_problem_with_upper_case_extentions()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');

        $image_name = json_decode($this->getBase64WithName('test.PNG'))->output->name;
        app(AddAsset::class)->add($article, json_decode($this->getBase64WithName('test.PNG'))->output->image, 'thumbnail', 'en', $image_name, $article);

        $this->assertEquals('test.png', $article->asset('thumbnail')->filename());
    }

    /** @test */
    public function addFile_returns_asset()
    {
        $article = $this->getArticleWithAsset('banner');

        $asset = app(AddAsset::class)->add($article, UploadedFile::fake()->image('imageFR.png'), 'banner', 'fr');

        $this->assertInstanceOf(Asset::class, $asset);
    }

    /** @test */
    public function softdeleting_model_will_set_pivot_to_unused()
    {
        ArticleWithSoftdelete::migrate();
        $article = $this->getSoftdeleteArticleWithAsset('banner');

        $article->delete();

        $this->assertEquals(1, DB::table('asset_pivots')->get()->first()->unused);
    }
}
