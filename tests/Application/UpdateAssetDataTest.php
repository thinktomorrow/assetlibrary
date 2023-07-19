<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
        $asset = $this->createAssetWithMedia();

        $this->assertEquals(null, $asset->getData('foo'));

        app(UpdateAssetData::class)->handle($asset->id, ['foo' => 'bar-updated']);

        $this->assertEquals('bar-updated', $asset->fresh()->getData('foo'));
    }

    public function test_it_can_update_asset_data_while_keeping_existing_data()
    {
        $asset = $this->createAssetWithMedia();

        $this->assertEquals(null, $asset->getData('foo'));

        app(UpdateAssetData::class)->handle($asset->id, ['foo' => 'barr']);
        $this->assertEquals('barr', $asset->fresh()->getData('foo'));

        app(UpdateAssetData::class)->handle($asset->id, ['bar' => 'bazz']);
        $this->assertEquals('barr', $asset->fresh()->getData('foo'));
        $this->assertEquals('bazz', $asset->fresh()->getData('bar'));
    }

    public function test_it_can_set_asset_data_to_null()
    {
        $asset = $this->createAssetWithMedia();

        $this->assertEquals(null, $asset->getData('foo'));

        app(UpdateAssetData::class)->handle($asset->id, ['foo' => 'barr']);
        $this->assertEquals('barr', $asset->fresh()->getData('foo'));

        app(UpdateAssetData::class)->handle($asset->id, ['foo' => null]);
        $this->assertEquals(null, $asset->fresh()->getData('foo'));
    }
}
