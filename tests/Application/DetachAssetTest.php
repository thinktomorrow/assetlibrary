<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\CreateAsset;
use Thinktomorrow\AssetLibrary\Application\DetachAsset;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class DetachAssetTest extends TestCase
{
    public function setUp(): void
    {
        parent:: setUp();

        Article:: migrate();
    }

    public function tearDown(): void
    {
        Artisan:: call('media-library:clear');

        parent:: tearDown();
    }

    /**
     * @test
     */
    public function it_can_detach_an_asset()
    {
        //upload a single image
        $article = $this->createModelWithAsset('image');

        app(DetachAsset::class)->detach($article, $article->asset('image')->id, 'image', 'en');

        $this->assertCount(1, Asset::all());
        $this->assertCount(0, $article->assetRelation()->get());
    }

    /**
     * @test
     */
    public function it_can_detach_an_asset_by_type()
    {
        //upload a single image
        $article = $this->createModelWithAsset('image');
        $asset = $this->createAssetWithMedia('image.png');
        app(CreateAsset::class)->add($article, $asset, 'banner', 'nl');

        app(DetachAsset::class)->detach($article, $asset->id, 'banner', 'nl');

        $this->assertCount(2, Asset::all());
        $this->assertCount(1, $article->assetRelation()->get());
    }

    /**
     * @test
     */
    public function it_can_detach_multiple_asset_from_model()
    {
        $article = $this->createModelWithAsset('image');

        $asset = $this->createAssetWithMedia('image.png');

        app(CreateAsset::class)->add($article, $asset, 'image', 'en');

        app(DetachAsset::class)->detach($article, [$article->asset('image')->id, $asset->id], 'image', 'en');

        $this->assertEquals(2, Asset::all()->count());
        $this->assertCount(0, $article->assetRelation()->get());
    }

    /**
     * @test
     */
    public function it_can_detach_all_assets_from_model()
    {
        $article = $this->createModelWithAsset('image');

        $asset = $this->createAssetWithMedia('image.png');

        app(CreateAsset::class)->add($article, $asset, 'banner', 'nl');

        app(DetachAsset::class)->detachAll($article);

        $this->assertEquals(2, Asset::all()->count());
        $this->assertCount(0, $article->assetRelation()->get());
    }

    /**
     * @test
     */
    public function it_can_detach_all_assets_with_type_from_model()
    {
        $article = $this->createModelWithAsset('image');

        $asset = $this->createAssetWithMedia('image.png');

        app(CreateAsset::class)->add($article, $asset, 'banner', 'nl');

        app(DetachAsset::class)->detachAll($article, 'banner');

        $this->assertEquals(2, Asset::all()->count());
        $this->assertCount(1, $article->assetRelation()->get());
    }
}
