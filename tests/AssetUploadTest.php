<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Application\AssetUploader;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetUploadTest extends TestCase
{
    public function tearDown(): void
    {
        Artisan::call('medialibrary:clear');
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_keep_original_source()
    {
        $source = UploadedFile::fake()->create('testSource.txt');

        // Second parameter is flag to preserve original source file
        $asset = AssetUploader::upload($source);

        $this->assertFileExists($source->getPath());
    }

    /** @test */
    public function it_can_upload_an_array_of_assets()
    {
        $assets = collect([]);

        $assets->push(AssetUploader::upload(UploadedFile::fake()->image('image1.png')));
        $assets->push(AssetUploader::upload(UploadedFile::fake()->image('image2.png')));
        $assets->push(UploadedFile::fake()->image('image2.png'));

        AssetUploader::upload($assets);

        $this->assertEquals(3, Asset::count());
    }
}
