<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\ReplaceMedia;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class ReplaceMediaTest extends TestCase
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

    public function test_it_can_replace_an_asset_media()
    {
        $asset = $this->createAssetWithMedia('image.png');
        $newAsset = $this->createAssetWithMedia('image-second.jpg');
        $originalDiskPath = $asset->getPath();

        $this->assertDatabaseCount('media', 2);

        app(ReplaceMedia::class)->handle(
            $asset->getFirstMedia(),
            $newAsset->getFirstMedia(),
        );

        $this->assertDatabaseCount('media', 1);

        $asset->refresh();

        $this->assertEquals(1, $asset->id);
        $this->assertEquals('image-second.jpg', $asset->getFileName());
        $this->assertEquals('/media/2/image-second.jpg', $asset->getUrl());
    }

    public function test_after_replace_the_new_asset_media_is_not_present_on_disk()
    {
        $asset = $this->createAssetWithMedia('image.png');
        $newAsset = $this->createAssetWithMedia('image-second.jpg');
        $originalDiskPath = $asset->getPath();

        app(ReplaceMedia::class)->handle(
            $asset->getFirstMedia(),
            $newAsset->getFirstMedia(),
        );

        $asset->refresh();

        $this->assertFileExists($asset->getPath());
        $this->assertFileDoesNotExist($originalDiskPath);
    }
}
