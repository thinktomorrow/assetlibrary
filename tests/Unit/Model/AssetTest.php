<?php

namespace Thinktomorrow\AssetLibrary\Tests\Unit\Model;

use Illuminate\Http\UploadedFile;
use Thinktomorrow\AssetLibrary\Application\CreateAsset;
use Thinktomorrow\AssetLibrary\Application\AssetUploader;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Exceptions\ConfigException;
use Thinktomorrow\AssetLibrary\Exceptions\CorruptMediaException;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetTest extends TestCase
{
    private Asset $asset;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'thumb' => [
                'width'     => 50,
                'height'    => 50,
            ],
        ]);

        $this->asset = Asset::create();
        $this->asset->addMedia(UploadedFile::fake()->image('test-image.jpg'))
            ->toMediaCollection();
    }

    public function test_asset_without_media_does_not_exist()
    {
        $this->assertFalse((new Asset())->exists());
        $this->assertFalse(Asset::create()->exists());
    }

    public function test_asset_exists()
    {
        $this->assertTrue($this->asset->exists());
    }

    public function test_asset_exists_for_conversion()
    {
        $this->assertTrue($this->asset->exists());
        $this->assertTrue($this->asset->exists('thumb'));
        $this->assertFalse($this->asset->exists('unknown'));
    }

    public function test_asset_without_media_has_no_filename()
    {
        $this->assertNull((new Asset())->getFileName());
        $this->assertNull(Asset::create()->getFileName());
    }

    public function test_asset_can_get_filename()
    {
        $this->assertEquals('test-image.jpg', $this->asset->getFileName());
    }

    public function test_asset_can_get_filename_for_conversion()
    {
        $this->assertEquals('test-image-thumb.jpg', $this->asset->getFileName('thumb'));
    }

    public function test_asset_uses_original_filename_when_conversion_does_not_exist()
    {
        $this->assertEquals('test-image.jpg', $this->asset->getFileName('unknown'));
    }

    public function test_asset_without_media_has_no_path()
    {
        $this->assertNull((new Asset())->getPath());
        $this->assertNull(Asset::create()->getPath());
    }

    public function test_asset_can_get_path()
    {
        $this->assertStringEndsWith('/temp/media/1/test-image.jpg', $this->asset->getPath());
    }

    public function test_asset_can_get_path_for_conversion()
    {
        $this->assertStringEndsWith('/temp/media/1/conversions/test-image-thumb.jpg', $this->asset->getPath('thumb'));
        $this->assertStringEndsWith('/temp/media/1/test-image.jpg', $this->asset->getPath('unknown'));
    }

    public function test_asset_without_media_has_no_url()
    {
        $this->assertNull((new Asset())->getUrl());
        $this->assertNull(Asset::create()->getUrl());
    }

    public function test_asset_can_get_url()
    {
        $this->assertEquals('/media/1/test-image.jpg', $this->asset->getUrl());
    }

    public function test_asset_can_get_url_for_conversion()
    {
        $this->assertEquals('/media/1/conversions/test-image-thumb.jpg', $this->asset->getUrl('thumb'));
        $this->assertEquals('/media/1/test-image.jpg', $this->asset->getUrl('unknown'));
    }

    public function test_asset_uses_original_url_when_conversion_does_not_exist()
    {
        $this->assertEquals('/media/1/test-image.jpg', $this->asset->getUrl('unknown'));
    }
}
