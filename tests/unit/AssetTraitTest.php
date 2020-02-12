<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\DeleteAsset;
use Thinktomorrow\AssetLibrary\Application\SortAssets;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\stubs\ArticleWithSoftdelete;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetTraitTest extends TestCase
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

        $this->assertEquals('', $article->asset('banner')->url());

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
