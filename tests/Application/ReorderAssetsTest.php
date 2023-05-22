<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\ReorderAssets;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class ReorderAssetsTest extends TestCase
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

    public function test_it_can_reorder_assets()
    {
        $asset = $this->createAssetWithMedia('foobar.pdf');
        $asset2 = $this->createAssetWithMedia('foobar.mp4');
        $asset3 = $this->createAssetWithMedia('foobar.xlsx');

        $model = $this->createModelWithAsset($asset, 'doc', 'en', 0, []);
        app(AddAsset::class)->handle($model, $asset2, 'doc', 'en', 0, []);
        app(AddAsset::class)->handle($model, $asset3, 'doc', 'en', 0, []);

        app(ReorderAssets::class)->handle($model, 'doc', 'en', [$asset3->id, $asset->id, $asset2->id]);

        $model->refresh();

        $assets = $model->assets('doc');
        $this->assertCount(3, $assets);
        $this->assertEquals($asset2->id, $assets->pop()->id);
        $this->assertEquals($asset->id, $assets->pop()->id);
        $this->assertEquals($asset3->id, $assets->pop()->id);
    }

    public function test_it_can_reorder_assets_with_a_given_order()
    {
        $asset = $this->createAssetWithMedia('foobar.pdf');
        $asset2 = $this->createAssetWithMedia('foobar.mp4');
        $asset3 = $this->createAssetWithMedia('foobar.xlsx');

        $model = $this->createModelWithAsset($asset, 'doc');
        app(AddAsset::class)->handle($model, $asset2, 'doc', 'en', 0, []);
        app(AddAsset::class)->handle($model, $asset3, 'doc', 'en', 0, []);

        app(ReorderAssets::class)->handle($model, 'doc', 'en', [ 2 => $asset->id, 4 => $asset2->id, 1 => $asset3->id]);

        $model->refresh();

        $assets = $model->assets('doc');
        $this->assertCount(3, $assets);
        $this->assertEquals($asset2->id, $assets->pop()->id);
        $this->assertEquals($asset->id, $assets->pop()->id);
        $this->assertEquals($asset3->id, $assets->pop()->id);
    }

    public function test_it_reorders_assets_per_type()
    {
        $asset = $this->createAssetWithMedia('foobar.pdf');
        $asset2 = $this->createAssetWithMedia('foobar.mp4');
        $asset3 = $this->createAssetWithMedia('foobar.xlsx');

        $model = $this->createModelWithAsset($asset, 'doc', 'en', 0, []);
        app(AddAsset::class)->handle($model, $asset2, 'xxx', 'en', 0, []);
        app(AddAsset::class)->handle($model, $asset3, 'doc', 'en', 0, []);

        app(ReorderAssets::class)->handle($model, 'doc', 'en', [$asset3->id, $asset->id, $asset2->id]);

        $model->refresh();

        $assets = $model->assets('doc');
        $this->assertCount(2, $assets);
        $this->assertEquals($asset->id, $assets->pop()->id);
        $this->assertEquals($asset3->id, $assets->pop()->id);
    }

    public function test_it_reorders_assets_per_locale()
    {
        $asset = $this->createAssetWithMedia('foobar.pdf');
        $asset2 = $this->createAssetWithMedia('foobar.mp4');
        $asset3 = $this->createAssetWithMedia('foobar.xlsx');

        $model = $this->createModelWithAsset($asset, 'doc', 'nl');
        app(AddAsset::class)->handle($model, $asset2, 'doc', 'nl', 0, []);
        app(AddAsset::class)->handle($model, $asset3, 'doc', 'nl', 0, []);
        app(AddAsset::class)->handle($model, $asset2, 'doc', 'en', 0, []);
        app(AddAsset::class)->handle($model, $asset3, 'doc', 'en', 0, []);

        app(ReorderAssets::class)->handle($model, 'doc', 'nl', [$asset3->id, $asset->id, $asset2->id]);
        app(ReorderAssets::class)->handle($model, 'doc', 'en', [$asset2->id, $asset3->id]);

        $model->refresh();

        $assets = $model->assets('doc', 'nl');
        $this->assertCount(3, $assets);

        $this->assertEquals($asset2->id, $assets->pop()->id);
        $this->assertEquals($asset->id, $assets->pop()->id);
        $this->assertEquals($asset3->id, $assets->pop()->id);

        $assets = $model->assets('doc', 'en');
        $this->assertCount(2, $assets);
        $this->assertEquals($asset3->id, $assets->pop()->id);
        $this->assertEquals($asset2->id, $assets->pop()->id);
    }
}
