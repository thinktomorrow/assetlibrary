<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AddAssetTest extends TestCase
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

    public function test_it_can_add_an_asset_to_a_model()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();

        $this->assertCount(0, $model->assetRelation()->get());

        app(AddAsset::class)->handle($model, $asset, 'doc', 'nl', 1, ['foo' => 'bar']);

        $this->assertCount(1, $model->assetRelation()->get());
    }

    public function test_it_can_read_the_pivot_data()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();

        $this->assertCount(0, $model->assetRelation()->get());

        app(AddAsset::class)->handle($model, $asset, 'doc', 'nl', 1, ['foo' => 'bar']);

        $this->assertEquals('doc', $model->asset(null, null)->pivot->type);
        $this->assertEquals('nl', $model->asset(null, null)->pivot->locale);
        $this->assertEquals(1, $model->asset(null, null)->pivot->order);
        $this->assertEquals('bar', $model->asset(null, null)->pivot->getData('foo'));
    }
}
