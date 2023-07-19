<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
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
}
