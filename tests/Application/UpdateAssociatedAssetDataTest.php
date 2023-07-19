<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\UpdateAssetData;
use Thinktomorrow\AssetLibrary\Application\UpdateAssociatedAssetData;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class UpdateAssociatedAssetDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function tearDown(): void
    {
        Artisan::call('media-library:clear');

        parent::tearDown();
    }

    public function test_it_can_update_asset_data()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();

        $this->assertCount(0, $model->assetRelation()->get());

        app(AddAsset::class)->handle($model, $asset, 'doc', 'nl', 1, ['foo' => 'bar']);

        $this->assertEquals('bar', $model->asset(null, null)->pivot->getData('foo'));

        app(UpdateAssociatedAssetData::class)->handle($model, $asset->id, 'doc', 'nl', ['foo' => 'bar-updated']);

        $this->assertEquals('bar-updated', $model->fresh()->asset(null, null)->pivot->getData('foo'));
    }

    public function test_it_can_update_asset_data_while_keeping_existing_data()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();

        $this->assertCount(0, $model->assetRelation()->get());

        app(AddAsset::class)->handle($model, $asset, 'doc', 'nl', 1, ['foo' => 'bar']);

        $this->assertEquals('bar', $model->asset(null, null)->pivot->getData('foo'));

        app(UpdateAssociatedAssetData::class)->handle($model, $asset->id, 'doc', 'nl', ['foo' => 'bar-updated']);
        $this->assertEquals('bar-updated', $model->fresh()->asset(null, null)->pivot->getData('foo'));

        app(UpdateAssociatedAssetData::class)->handle($model, $asset->id, 'doc', 'nl', ['baz' => 'bazz']);
        $this->assertEquals('bar-updated', $model->fresh()->asset(null, null)->pivot->getData('foo'));
        $this->assertEquals('bazz', $model->fresh()->asset(null, null)->pivot->getData('baz'));
    }

    public function test_it_can_set_asset_data_to_null()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();

        $this->assertCount(0, $model->assetRelation()->get());

        app(AddAsset::class)->handle($model, $asset, 'doc', 'nl', 1, ['foo' => 'bar']);

        $this->assertEquals('bar', $model->asset(null, null)->pivot->getData('foo'));

        app(UpdateAssociatedAssetData::class)->handle($model, $asset->id, 'doc', 'nl', ['foo' => 'bar-updated']);
        $this->assertEquals('bar-updated', $model->fresh()->asset(null, null)->pivot->getData('foo'));

        app(UpdateAssociatedAssetData::class)->handle($model, $asset->id, 'doc', 'nl', ['foo' => null]);
        $this->assertEquals(null, $model->fresh()->asset(null, null)->pivot->getData('foo'));
    }
}
