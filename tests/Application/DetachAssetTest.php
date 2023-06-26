<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Thinktomorrow\AssetLibrary\Application\DetachAsset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class DetachAssetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function test_it_can_detach_an_asset()
    {
        $model = $this->createModelWithAsset($asset = $this->createAssetWithMedia());

        $this->assertDatabaseCount('assets', 1);
        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseCount('assets_pivot', 1);

        app(DetachAsset::class)->handle($model, 'image', 'en', [$asset->id]);

        $this->assertDatabaseCount('assets', 1);
        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseCount('assets_pivot', 0);
    }

    public function test_it_can_detach_all_assets()
    {
        $model = $this->createModelWithAsset($asset = $this->createAssetWithMedia());
        $model->assetRelation()->attach($this->createAssetWithMedia(), ['type' => 'image', 'locale' => 'nl', 'order' => 1]);
        $model->assetRelation()->attach($this->createAssetWithMedia('foobar.pdf'), ['type' => 'doc', 'locale' => 'nl', 'order' => 2]);

        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('assets_pivot', 3);

        app(DetachAsset::class)->handleAll($model);

        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('assets_pivot', 0);
    }

    public function test_it_detaches_all_assets_by_type()
    {
        $model = $this->createModelWithAsset($asset = $this->createAssetWithMedia());
        $model->assetRelation()->attach($asset2 = $this->createAssetWithMedia(), ['type' => 'image', 'locale' => 'nl', 'order' => 1]);
        $model->assetRelation()->attach($this->createAssetWithMedia('foobar.pdf'), ['type' => 'doc', 'locale' => 'nl', 'order' => 2]);

        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('assets_pivot', 3);

        app(DetachAsset::class)->handleByType($model, 'image');

        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('assets_pivot', 1);
    }

    public function test_it_detaches_an_asset_by_type_and_locale()
    {
        $model = $this->createModelWithAsset($asset = $this->createAssetWithMedia());
        $model->assetRelation()->attach($asset2 = $this->createAssetWithMedia(), ['type' => 'image', 'locale' => 'nl', 'order' => 1]);
        $model->assetRelation()->attach($this->createAssetWithMedia('foobar.pdf'), ['type' => 'doc', 'locale' => 'nl', 'order' => 2]);

        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('assets_pivot', 3);

        app(DetachAsset::class)->handle($model, 'image', 'en', [$asset->id]);

        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('assets_pivot', 2);

        app(DetachAsset::class)->handle($model, 'image', 'nl', [$asset2->id]);

        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('assets_pivot', 1);
    }

    public function test_it_detaches_an_asset_when_type_exists()
    {
        $model = $this->createModelWithAsset($asset = $this->createAssetWithMedia('foobar.pdf'), 'doc', 'en');

        $this->assertDatabaseCount('assets_pivot', 1);

        app(DetachAsset::class)->handle($model, 'image', 'en', [$asset->id]);

        $this->assertDatabaseCount('assets_pivot', 1);
    }

    public function test_it_can_detach_an_asset_when_model_is_deleted()
    {
        $model = $this->createModelWithAsset($asset = $this->createAssetWithMedia());

        $this->assertDatabaseCount('assets', 1);
        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseCount('assets_pivot', 1);

        $model->delete();

        $this->assertDatabaseCount('assets', 1);
        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseCount('assets_pivot', 0);
    }
}
