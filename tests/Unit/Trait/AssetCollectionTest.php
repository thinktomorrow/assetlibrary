<?php

namespace Thinktomorrow\AssetLibrary\Tests\Unit\Trait;

use Illuminate\Support\Collection;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetCollectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function test_it_returns_empty_collection_when_assets_are_not_found()
    {
        $model = Article::create();
        $this->assertCount(0, $model->assets('unknown'));

        $model = $this->createModelWithAsset($this->createAssetWithMedia());
        $this->assertCount(0, $model->assets('unknown'));
    }

    public function test_it_returns_null_when_asset_is_not_found()
    {
        $model = Article::create();
        $this->assertNull($model->asset('unknown'));

        $model = $this->createModelWithAsset($this->createAssetWithMedia());
        $this->assertNull($model->asset('unknown'));
    }

    public function test_it_can_get_assets()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $this->assertCount(1, $model->assets('image'));
        $this->assertInstanceOf(Collection::class, $model->assets('image'));
    }

    public function test_it_can_get_asset()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $this->assertInstanceOf(Asset::class, $model->asset('image'));
    }

    public function test_it_can_get_assets_without_defining_collection_type()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $this->assertCount(1, $model->assets());
        $this->assertInstanceOf(Collection::class, $model->assets());
        $this->assertInstanceOf(Asset::class, $model->asset());
    }

    public function test_it_can_have_same_asset_multiple_times()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();
        $model->assetRelation()->attach($asset, ['type' => 'xxx', 'locale' => 'nl', 'order' => 0]);
        $model->assetRelation()->attach($asset, ['type' => 'yyy', 'locale' => 'nl', 'order' => 0]);

        $this->assertCount(2, $model->assets(null, null));
    }

    public function test_it_can_have_different_types()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();
        $model->assetRelation()->attach($asset, ['type' => 'product-image', 'locale' => 'nl', 'order' => 0]);
        $model->assetRelation()->attach($asset, ['type' => 'general-image', 'locale' => 'nl', 'order' => 0]);

        $this->assertCount(2, $model->assets(null, null));
        $this->assertCount(1, $model->assets('product-image', null));
        $this->assertCount(1, $model->assets('general-image', null));
        $this->assertCount(0, $model->assets('unknown-image', null));
    }

    public function test_it_retrieves_assets_by_order()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();
        $asset2 = $this->createAssetWithMedia('foobar.pdf');
        $model->assetRelation()->attach($asset, ['type' => 'image', 'locale' => 'nl', 'order' => 1]);
        $model->assetRelation()->attach($asset2, ['type' => 'doc', 'locale' => 'nl', 'order' => 0]);

        $this->assertEquals($asset2->id, $model->asset(null, null)->id);
        $this->assertEquals($asset2->id, $model->asset('doc', null)->id);

        $this->assertEquals($asset2->id, $model->assets(null, null)[0]->id);
        $this->assertEquals($asset->id, $model->assets(null, null)[1]->id);
    }
}
