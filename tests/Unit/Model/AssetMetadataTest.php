<?php

namespace Thinktomorrow\AssetLibrary\Tests\Unit\Model;

use Illuminate\Http\UploadedFile;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetMetadataTest extends TestCase
{
    public function test_asset_can_get_file_metadata()
    {
        $asset = Asset::create();
        $asset->addMedia(UploadedFile::fake()->create('sample.txt', 'this is some content for testing.'))
            ->toMediaCollection();

        $this->assertStringEndsWith('/temp/media/1/sample.txt', $asset->getPath());
        $this->assertEquals('sample.txt', $asset->getFileName());
        $this->assertEquals(33, $asset->getSize());
        $this->assertEquals('33 B', $asset->getHumanReadableSize());
        $this->assertEquals('text/plain', $asset->getMimeType());
        $this->assertEquals('txt', $asset->getExtension());
        $this->assertEquals('file', $asset->getExtensionType());
        $this->assertNull($asset->getImageWidth());
        $this->assertNull($asset->getImageHeight());
    }

    public function test_asset_can_get_image_metadata()
    {
        $asset = Asset::create();
        $asset->addMedia(UploadedFile::fake()->image('test-image.jpg', 50, 50))
            ->toMediaCollection();

        $this->assertStringEndsWith('/temp/media/1/test-image.jpg', $asset->getPath());
        $this->assertStringEndsWith('/temp/media/1/conversions/test-image-thumb.jpg', $asset->getPath('thumb'));
        $this->assertEquals('test-image.jpg', $asset->getFileName());
        $this->assertEquals(755, $asset->getSize());
        $this->assertEquals('755 B', $asset->getHumanReadableSize());
        $this->assertEquals('image/jpeg', $asset->getMimeType());
        $this->assertEquals('jpg', $asset->getExtension());
        $this->assertEquals('image', $asset->getExtensionType());
        $this->assertEquals(50, $asset->getImageWidth());
        $this->assertEquals(50, $asset->getImageHeight());
    }

    public function test_asset_can_get_svg_metadata()
    {
        $asset = $this->createAssetWithMedia('logo.svg');

        $this->assertStringEndsWith('/temp/media/1/logo.svg', $asset->getPath());
        $this->assertEquals('logo.svg', $asset->getFileName());
        $this->assertEquals(3688, $asset->getSize());
        $this->assertEquals('3.6 KB', $asset->getHumanReadableSize());
        $this->assertEquals('image/svg+xml', $asset->getMimeType());
        $this->assertEquals('svg', $asset->getExtension());
        $this->assertEquals('image', $asset->getExtensionType());
        $this->assertNull($asset->getImageWidth());
        $this->assertNull($asset->getImageHeight());
    }

    public function test_asset_can_get_mp4_metadata()
    {
        $asset = $this->createAssetWithMedia('foobar.mp4');

        $this->assertEquals('/media/1/foobar.mp4', $asset->getUrl());
        $this->assertStringEndsWith('/temp/media/1/foobar.mp4', $asset->getPath());
        $this->assertStringEndsWith('/temp/media/1/foobar.mp4', $asset->getPath('thumb'));
        $this->assertEquals('foobar.mp4', $asset->getFileName());
        $this->assertEquals(1570024, $asset->getSize());
        $this->assertEquals('1.5 MB', $asset->getHumanReadableSize());
        $this->assertEquals('video/mp4', $asset->getMimeType());
        $this->assertEquals('mp4', $asset->getExtension());
        $this->assertEquals('video', $asset->getExtensionType());
    }

    public function test_it_can_return_the_url_for_xlsx()
    {
        $asset = $this->createAssetWithMedia('foobar.xlsx');

        $this->assertEquals('/media/1/foobar.xlsx', $asset->getUrl());
        $this->assertStringEndsWith('/temp/media/1/foobar.xlsx', $asset->getPath());
        $this->assertStringEndsWith('/temp/media/1/foobar.xlsx', $asset->getPath('thumb'));
        $this->assertEquals('foobar.xlsx', $asset->getFileName());
        $this->assertEquals(6432, $asset->getSize());
        $this->assertEquals('6.28 KB', $asset->getHumanReadableSize());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $asset->getMimeType());
        $this->assertEquals('xlsx', $asset->getExtension());
        $this->assertEquals('spreadsheet', $asset->getExtensionType());
    }
}
