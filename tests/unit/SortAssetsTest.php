<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Thinktomorrow\AssetLibrary\Asset;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\SortAssets;

class SortAssetsTest extends TestCase
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


        app(SortAssets::class)->handle($article, [(string) $asset3->id, (string) $asset1->id, (string) $asset2->id], 'banner', 'en');

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

        app(SortAssets::class)->handle($article, [5 => (string) $asset3->id, 1 => (string) $asset1->id, 9 => (string) $asset2->id], 'banner', 'en');

        $images = $article->assets('banner');

        $this->assertCount(3, $images);
        $this->assertEquals($asset2->id, $images->pop()->id);
        $this->assertEquals($asset3->id, $images->pop()->id);
        $this->assertEquals($asset1->id, $images->pop()->id);
    }

    /** @test */
    public function it_can_sort_images_per_type()
    {
        $article = Article::create();

        $asset1 = Asset::create();
        app(AddAsset::class)->add($article, $asset1, 'banner', 'en');

        $asset2 = Asset::create();
        app(AddAsset::class)->add($article, $asset2, 'banner', 'en');

        $asset3 = Asset::create();
        app(AddAsset::class)->add($article, $asset3, 'other', 'en');

        app(SortAssets::class)->handle($article, [(string) $asset1->id, (string) $asset2->id], 'banner', 'en');

        $images = $article->assets('banner');

        $this->assertCount(2, $images);
        $this->assertEquals($asset2->id, $images->pop()->id);
        $this->assertEquals($asset1->id, $images->pop()->id);
    }

    /** @test */
    public function it_can_sort_images_per_locale()
    {
        $article = Article::create();

        $asset1 = Asset::create();
        app(AddAsset::class)->add($article, $asset1, 'banner', 'en');

        $asset2 = Asset::create();
        app(AddAsset::class)->add($article, $asset2, 'banner', 'en');

        $asset3 = Asset::create();
        app(AddAsset::class)->add($article, $asset3, 'banner', 'nl');

        $asset4 = Asset::create();
        app(AddAsset::class)->add($article, $asset4, 'banner', 'nl');

        app(SortAssets::class)->handle($article, [(string) $asset1->id, (string) $asset2->id], 'banner', 'en');
        app(SortAssets::class)->handle($article, [(string) $asset4->id, (string) $asset3->id], 'banner', 'nl');

        $nl_images = $article->assets('banner', 'nl');
        $en_images = $article->assets('banner', 'en');

        $this->assertCount(2, $nl_images);
        $this->assertCount(2, $en_images);
        $this->assertEquals($asset2->id, $en_images->pop()->id);
        $this->assertEquals($asset1->id, $en_images->pop()->id);
        $this->assertEquals($asset3->id, $nl_images->pop()->id);
        $this->assertEquals($asset4->id, $nl_images->pop()->id);
    }
}
