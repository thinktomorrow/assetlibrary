<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\UpdateAssetData;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class UpdateAssetDataTest extends TestCase
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

        app(UpdateAssetData::class)->handle($model, $asset, 'doc', 'nl', ['foo' => 'bar-updated']);

        $this->assertEquals('bar-updated', $model->asset(null, null)->pivot->getData('foo'));
    }
}
