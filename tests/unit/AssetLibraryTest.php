<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Models\AssetLibrary;

class AssetLibraryTest extends TestCase
{

    public function tearDown(): void
    {
        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_remove_an_image()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        $asset2 = $this->getUploadedAsset('image.png');

        $this->assertEquals($asset2->getFilename(), 'image.png');
        $this->assertEquals($asset2->getImageUrl(), '/media/2/image.png');

        AssetLibrary::removeByIds($asset->id);

        $this->assertEquals(1, AssetLibrary::getAllAssets()->count());
        $this->assertEquals($asset2->id, AssetLibrary::getAllAssets()->first()->id);
    }

     /**
     * @test
     */
    public function it_can_handle_invalid_inputs_to_remove_function()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        AssetLibrary::removeByIds([null]);
        AssetLibrary::removeByIds(null);

        $this->assertEquals(1, AssetLibrary::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_can_remove_multiple_images()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();

        $this->assertEquals($asset->getFilename(), 'image.png');
        $this->assertEquals($asset->getImageUrl(), '/media/1/image.png');

        $asset2 = $this->getUploadedAsset('image.png');

        $this->assertEquals($asset2->getFilename(), 'image.png');
        $this->assertEquals($asset2->getImageUrl(), '/media/2/image.png');

        AssetLibrary::removeByIds([$asset->id, $asset2->id]);

        $this->assertEquals(0, AssetLibrary::getAllAssets()->count());
    }

    /**
     * @test
     */
    public function it_doesnt_remove_the_asset_if_you_dont_have_permissions()
    {
        //upload a single image
        $asset = $this->getUploadedAsset();
        $dir   = public_path($asset->getFileUrl());

        @chmod($dir, 0444);

        $this->assertFileExists($dir);
        $this->assertFileIsReadable($dir);
        $this->assertFileNotIsWritable($dir);

        AssetLibrary::removeByIds($asset->id);

        $this->assertEquals(1, AssetLibrary::getAllAssets()->count());
        $this->assertCount(1, $asset->fresh()->media);

        @chmod($dir, 0777);
        AssetLibrary::removeByIds($asset->id);
    }
}
