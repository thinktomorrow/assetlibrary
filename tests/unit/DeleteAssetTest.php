<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Thinktomorrow\AssetLibrary\Asset;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Application\DeleteAsset;
use Thinktomorrow\AssetLibrary\Exceptions\FileNotAccessibleException;

class DeleteAssetTest extends TestCase
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

       $this->assertEquals($asset->filename(), 'image.png');
       $this->assertEquals($asset->url(), '/media/1/image.png');

       $asset2 = $this->getUploadedAsset('image.png');

       $this->assertEquals($asset2->filename(), 'image.png');
       $this->assertEquals($asset2->url(), '/media/2/image.png');

       app(DeleteAsset::class)->delete($asset->id);

       $this->assertEquals(1, Asset::all()->count());
       $this->assertEquals($asset2->id, Asset::all()->first()->id);
   }

   /**
    * @test
    */
   public function it_can_handle_invalid_inputs_to_remove_function()
   {
       $asset = $this->getUploadedAsset();

       $this->assertEquals($asset->filename(), 'image.png');
       $this->assertEquals($asset->url(), '/media/1/image.png');

       app(DeleteAsset::class)->delete([null]);
       app(DeleteAsset::class)->delete(null);

       $this->assertEquals(1, Asset::all()->count());
   }

   /**
    * @test
    */
   public function it_can_remove_multiple_images()
   {
       //upload a single image
       $asset = $this->getUploadedAsset();

       $this->assertEquals($asset->filename(), 'image.png');
       $this->assertEquals($asset->url(), '/media/1/image.png');

       $asset2 = $this->getUploadedAsset('image.png');

       $this->assertEquals($asset2->filename(), 'image.png');
       $this->assertEquals($asset2->url(), '/media/2/image.png');

       app(DeleteAsset::class)->delete([$asset->id, $asset2->id]);

       $this->assertEquals(0, Asset::all()->count());
   }

   /**
    * @test
    */
   public function it_doesnt_remove_the_asset_if_you_dont_have_permissions()
   {
       $this->expectException(FileNotAccessibleException::class);

       //upload a single image
       $asset = $this->getUploadedAsset();
       $dir   = public_path($asset->url());

       @chmod($dir, 0444);

       $this->assertFileExists($dir);
       $this->assertFileIsReadable($dir);
       $this->assertFileNotIsWritable($dir);

       app(DeleteAsset::class)->delete($asset->id);

       $this->assertEquals(1, Asset::all()->count());
       $this->assertCount(1, $asset->fresh()->media);

       @chmod($dir, 0777);
       app(DeleteAsset::class)->delete($asset->id);
   }
}
