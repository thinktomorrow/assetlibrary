<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;

class AssetUploadTest extends TestCase
{
    /** @test */
    public function it_can_keep_original_source()
    {
        $source = UploadedFile::fake()->create('testSource.txt');

        // Second parameter is flag to preserve original source file
        $asset = AssetUploader::upload($source, false);

        $this->assertFileExists($source->getPath());

        // Cleanup
        $asset->delete(); // remove uploaded asset
    }
}
