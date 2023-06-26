<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Thinktomorrow\AssetLibrary\Application\DeleteAsset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class DeleteAssetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function test_it_can_remove_an_asset()
    {
        $asset = $this->createAssetWithMedia();

        $this->assertDatabaseCount('assets', 1);

        app(DeleteAsset::class)->handle($asset);

        $this->assertDatabaseCount('assets', 0);
    }

    public function test_it_can_remove_an_associated_asset()
    {
        $model = $this->createModelWithAsset($asset = $this->createAssetWithMedia());

        $this->assertCount(1, $model->assets());

        app(DeleteAsset::class)->handle($model->asset());

        $model->refresh();

        $this->assertCount(0, $model->assets());
    }

    public function test_it_removes_media_as_well()
    {
        $this->createModelWithAsset($asset = $this->createAssetWithMedia());

        $this->assertDatabaseCount('assets', 1);
        $this->assertDatabaseCount('assets_pivot', 1);
        $this->assertDatabaseCount('media', 1);

        app(DeleteAsset::class)->handle($asset);

        $this->assertDatabaseCount('assets', 0);
        $this->assertDatabaseCount('assets_pivot', 0);
        $this->assertDatabaseCount('media', 0);
    }

    public function test_it_removes_file_on_disk_as_well()
    {
        $this->createModelWithAsset($asset = $this->createAssetWithMedia());

        $this->assertFileExists($path = $asset->getPath());

        app(DeleteAsset::class)->handle($asset);

        $this->assertFileDoesNotExist($path);
    }
}
